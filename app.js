var express = require('express');
var app = express();
var server = require('http').Server(app);
var io = require('socket.io')(server);

server.listen(3000 , function(){ console.log(' Im Listening ' )});

app.get('/', function (req, res) {
  //console.log(req, res);
  res.sendFile(__dirname + '/');
});

io.on('connection', function (socket) {
	socket.on('room', function(r) {
		if (r) {
			socket.join(r);
		} 

		return;
	});

	socket.on('message', function (msg) {
		var ROOMS = Object.keys(socket.rooms);
		console.log(ROOMS[1]);
		io.in(ROOMS[1]).emit('message', msg);
	})
	socket.emit('news', { hello: 'world' });
	socket.on('update', function (data) {
    		console.log(data);
		io.emit('update', data);
  	});
});

room = "abc123";
