let newman = require('newman');
require('dotenv').config()

newman.run({
    collection: process.env.POSTMAN_COLLECTION || "https://www.getpostman.com/collections/1b5ed0dd17a980aea002",
    environment: require(`./${process.env.POSTMAN_ENV || "LOCAL"}_ENV.json`),
    reporters: "cli"
}, function(err){
    if (err) {
        throw err
    }
    console.log('Collection run complete!')
})