/**
 * node index.js
 * - A simple echo test.
 */


 var io = require('socket.io-client');

 var socket = io.connect( 'http://0.0.0.0:9090',{
    channel       : 'my_mobile_app',
    presence      : false, // DISABLE PRESENCE HERE
    publish_key   : 'demo',
    subscribe_key : 'demo'
} );

 socket.on( 'connect', function() {
     console.log('Connection Established! Ready to send/receive data!');
     socket.send('my message here');
     socket.send(1234567);
     socket.send([1,2,3,4,5]);
     socket.send({ apples : 'bananas' });
 } );


/**

var WebSocket = require('ws');
var ws = new WebSocket('ws://0.0.0.0:9090');

ws.on('open', function open() {
  console.log('connected');
  ws.send(Date.now().toString(), {mask: true});
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

*/
