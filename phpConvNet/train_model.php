<!DOCTYPE html>
<html>
<head>
    <title>Model Parameters</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #b1ecfe;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .parameter-input {
            margin-bottom: 10px;
        }

        #result {

        }

        #process-button {
            background-color: pink;
            outline-color: #ffc8d8;
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="mb-4">Model Parameters</h1>
    <label for="num-epochs">Number of Epochs:</label>
    <input id="num-epochs" class="parameter-input" type="number" value="10">
    <label for="num-conv-layers">Number of Convolutional Layers:</label>
    <input id="num-conv-layers" class="parameter-input" type="number" value="1">
    <button id="process-button" class="btn btn-primary">Process</button>
    <div id="result" class="mt-3"></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@3.9.0/dist/tf.min.js"></script>
<script>
    async function retrieveData(chunkIndex) {
        const response = await fetch(`http://localhost:3000/api/retrieveData/train/${chunkIndex}`);
        const data = await response.json();
        return data;
    }

    async function retrieveTestData() {
        const response = await fetch('http://localhost:3000/api/retrieveData/test/0');
        const data = await response.json();
        return data;
    }

    async function createConvNet(numConvLayers) {
        const model = tf.sequential();

        for(let i=0; i<numConvLayers; i++) {
            model.add(tf.layers.conv2d({ filters: 16, kernelSize: 3, activation: 'relu', inputShape: i == 0 ? [100, 100, 3] : undefined }));
            model.add(tf.layers.maxPooling2d({ poolSize: 2 }));
        }

        model.add(tf.layers.flatten());
        model.add(tf.layers.dense({ units: 64, activation: 'relu' }));
        model.add(tf.layers.dense({ units: 2, activation: 'softmax' }));
        model.compile({ loss: 'sparseCategoricalCrossentropy', optimizer: 'adam', metrics: ['accuracy'] });

        return model;
    }


    async function processData() {
        const numEpochs = parseInt(document.getElementById('num-epochs').value);
        const numConvLayers = parseInt(document.getElementById('num-conv-layers').value);
        const model = await createConvNet(numConvLayers);

        // Fetch all test data at once
        const testData = await retrieveTestData();
        const testXs = tf.tensor4d(testData.data, [testData.data.length, 100, 100, 3]);
        const testYs = tf.tensor1d(testData.labels, 'int32');

        let chunkIndex = 0;
        let accuracy = 0;
        let valAccuracy = 0;
        let loss = 0;
        let valLoss = 0;

        while (true) {
            const trainData = await retrieveData(chunkIndex);
            if (!trainData.data.length) break;

            const trainXs = tf.tensor4d(trainData.data, [trainData.data.length, 100, 100, 3]);
            const trainYs = tf.tensor1d(trainData.labels, 'int32');

            // Pass the test data as validation data to model.fit()
            const history = await model.fit(trainXs, trainYs, { epochs: numEpochs, validationData: [testXs, testYs] });
            accuracy = history.history.acc[history.history.acc.length - 1];
            valAccuracy = history.history.val_acc[history.history.val_acc.length - 1];
            loss = history.history.loss[history.history.loss.length - 1];
            valLoss = history.history.val_loss[history.history.val_loss.length - 1];

            document.getElementById('result').innerText = `Training accuracy: ${accuracy.toFixed(3)}\nValidation accuracy: ${valAccuracy.toFixed(3)}\nTraining loss: ${loss.toFixed(4)}\nValidation loss: ${valLoss.toFixed(4)}`;

            chunkIndex++;
        }


        const trainAccuracy = accuracy.toFixed(3);
        const testAccuracy = valAccuracy.toFixed(3);
        const params = `epochs: ${numEpochs}, layers: ${numConvLayers}`;


        const formData = new FormData();
        formData.append('trainAccuracy', trainAccuracy);
        formData.append('testAccuracy', testAccuracy);
        formData.append('params', params);


        const request = await fetch('add_results.php', {
            method: 'POST',
            body: formData
        });


        if (request.ok) {
            const response = await request.text();
            document.getElementById('result').innerText = 'Model results added successfully. ' + response;
        } else {
            document.getElementById('result').innerText = 'Error adding model results.';
        }
    }

    document.getElementById('process-button').addEventListener('click', processData);
</script>
</body>
</html>