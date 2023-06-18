var mysql = require('mysql');
const fs = require('fs');
const tf = require('@tensorflow/tfjs-node');

var connection = mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'image_data'
});

let trainData = [];
let trainLabels = [];
let testData = [];
let testLabels = [];
let mobilenet;

async function loadMobileNetFeatureModel() {
    const URL =
        'https://tfhub.dev/google/tfjs-model/imagenet/mobilenet_v3_small_100_224/feature_vector/5/default/1';
    mobilenet = await tf.loadGraphModel(URL, { fromTFHub: true });
    console.log('Successfully loaded model');

    tf.tidy(function () {
        let answer = mobilenet.predict(tf.zeros([1, 224, 224, 3]));
        console.log(answer.shape);
    });
}

let model = tf.sequential();
model.add(tf.layers.dense({ inputShape: [1024], units: 128, activation: 'relu' }));
model.add(tf.layers.dense({ units: 2, activation: 'softmax' }));

model.summary();

model.compile({
    optimizer: 'adam',
    loss: 'binaryCrossentropy',
    metrics: ['accuracy']
});

connection.connect();

async function retrieveData(table, callback) {
    connection.query(`SELECT path, label FROM ${table}_images JOIN ${table}_labels ON ${table}_images.img_id = ${table}_labels.id`, async function (error, results, fields) {
        if (error) throw error;

        for (const result of results) {
            const imageBuffer = fs.readFileSync(result.path);
            let tfImage = tf.node.decodeImage(imageBuffer, 3);
            const resizedImage = tf.image.resizeBilinear(tfImage, [224, 224]);
            const offset = tf.scalar(127.5);
            const normalizedImage = resizedImage.sub(offset).div(offset);
            const batchedImage = normalizedImage.expandDims(0);

            if (JSON.stringify(batchedImage.shape) !== JSON.stringify([1, 224, 224, 3])) {
                console.log(`Image at path ${result.path} has incorrect dimensions: ${JSON.stringify(batchedImage.shape)}`);
            } else {
                const prediction = mobilenet.predict(batchedImage).dataSync(); // Extract features from image
                if (table === 'train') {
                    trainData.push(prediction);
                    trainLabels.push(result.label);
                } else if (table === 'test') {
                    testData.push(prediction);
                    testLabels.push(result.label);
                }
            }
        }

        callback();
    });
}

loadMobileNetFeatureModel().then(() => {
    retrieveData('train', async function () {
        retrieveData('test', async function () {
            const trainXs = tf.tensor2d(trainData);
            const trainYs = tf.oneHot(tf.tensor1d(trainLabels).toInt(), 2);

            const testXs = tf.tensor2d(testData);
            const testYs = tf.oneHot(tf.tensor1d(testLabels).toInt(), 2);

            // Train the model
            await model.fit(trainXs, trainYs, {
                epochs: 20, // Number of epochs to train
                validationData: [testXs, testYs], // Use test data as validation set
                shuffle: true,
            });

            console.log('Saving the model...');
            await model.save('file://my-model');

            connection.end();
        });
    });
});
