/**
 * node index.js
 * - A simple echo test.
 */


var WebSocket = require('ws');
var ws = new WebSocket('ws://0.0.0.0:8080');

ws.on('open', function open() {
  console.log('connected');
});

ws.on('close', function close() {
  console.log('disconnected');
});

ws.on('message', function message(data, flags) {
  console.log(data);

  setTimeout(function timeout() {
    ws.send(Date.now().toString(), {mask: true});
  }, 500);
});


