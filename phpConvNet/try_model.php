<!DOCTYPE html>
<html>
<head>
    <title>Image Processing</title>
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

        .custom-file-input {
            cursor: pointer;
        }

        #image-preview {
            max-width: 400px;
            margin-bottom: 20px;
        }

        #result {
            font-weight: bold;
        }

        #process-button {
            font-size: 1.5em;
            background-color: pink;

        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="mb-4">Choose an image</h1>
    <div class="input-group mb-3">
        <div class="custom-file">
            <input type="file" class="custom-file-input" id="image-input" accept="image/*">
            <label class="custom-file-label" for="image-input">Choose Image</label>
        </div>
    </div>
    <div id="image-preview"></div>
    <button id="process-button" class="btn btn-primary">Predict</button>
    <div id="result" class="mt-3"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@3.12.0"></script>

<script>
    const imageInput = document.getElementById('image-input');
    const imagePreview = document.getElementById('image-preview');
    const processButton = document.getElementById('process-button');
    const result = document.getElementById('result');

    let mobilenet;
    let myModel;


    async function loadMobileNet() {
        const URL = 'https://tfhub.dev/google/tfjs-model/imagenet/mobilenet_v3_small_100_224/feature_vector/5/default/1';
        mobilenet = await tf.loadGraphModel(URL, { fromTFHub: true });
    }


    async function loadMyModel() {
        myModel = await tf.loadLayersModel('my-model/model.json');
    }


    imageInput.addEventListener('change', () => {
        const file = imageInput.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.createElement('img');
                img.onload = function () {
                    URL.revokeObjectURL(img.src); // Release the object URL
                    imagePreview.innerHTML = '';
                    imagePreview.appendChild(img);
                };
                img.src = URL.createObjectURL(file);
                img.classList.add('img-fluid');
            };
            reader.readAsDataURL(file);
        }
    });


    async function processImage(file) {
        const img = await tf.browser.fromPixels(imagePreview.firstChild);
        const resizedImg = tf.image.resizeBilinear(img, [224, 224]);
        const normalizedImg = resizedImg.div(255.0);
        const batchedImg = normalizedImg.expandDims(0);


        const mobilenetFeatures = mobilenet.predict(batchedImg);


        const prediction = myModel.predict(mobilenetFeatures);
        const predictionArray = await prediction.array();
        const predictionIndex = predictionArray[0].indexOf(Math.max(...predictionArray[0]));


        if (predictionIndex === 0) {
            result.textContent = 'Prediction: Dog';
        } else if (predictionIndex === 1) {
            result.textContent = 'Prediction: Cat';
        } else {
            result.textContent = 'Prediction: Unknown';
        }
    }


    processButton.addEventListener('click', () => {
        const file = imageInput.files[0];
        if (file) {
            processImage(file);
        }
    });


    Promise.all([loadMobileNet(), loadMyModel()]);
</script>
</body>
</html>
