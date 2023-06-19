const express = require('express');
const fs = require('fs');
const tf = require('@tensorflow/tfjs-node');
const mysql = require('mysql');

const app = express();
const port = 3000;

// Enable CORS
app.use((req, res, next) => {
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');
    next();
});

const connection = mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'image_data'
});

const CHUNK_SIZE = 40;

app.get('/api/retrieveData/:table/:chunkIndex', async (req, res) => {
    const { table, chunkIndex } = req.params;

    try {
        const data = await retrieveDataFromDB(table, chunkIndex);
        res.json(data);
    } catch (error) {
        console.error(error);
        res.status(500).json({ error: 'Internal Server Error' });
    }
});

async function retrieveDataFromDB(table, chunkIndex) {
    return new Promise((resolve, reject) => {
        const start = chunkIndex * CHUNK_SIZE;
        const end = CHUNK_SIZE;

        connection.query(`SELECT path, label FROM ${table}_images JOIN ${table}_labels ON ${table}_images.img_id = ${table}_labels.id LIMIT ?, ?`, [start, end], async function (error, results, fields) {
            if (error) reject(error);

            const data = [];
            const labels = [];

            for (const result of results) {
                const imageBuffer = fs.readFileSync(result.path);
                let tfImage = tf.node.decodeImage(imageBuffer, 3);
                const resizedImage = tf.image.resizeBilinear(tfImage, [100, 100]);
                const offset = tf.scalar(127.5);
                const normalizedImage = resizedImage.sub(offset).div(offset);
                const batchedImage = normalizedImage.expandDims(0);
                data.push(...batchedImage.arraySync());

                labels.push(result.label);
            }

            const batchedData = data;
            const batchedLabels = labels;

            resolve({ data: batchedData, labels: batchedLabels });
        });
    });
}

app.listen(port, () => {
    console.log(`Server running on port ${port}`);
});
