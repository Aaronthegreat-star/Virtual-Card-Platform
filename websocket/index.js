const http = require('http');
const { Server } = require("socket.io");

// Create HTTP server with maxHeaderSize limit (8 KB)
const httpServer = http.createServer({ maxHeaderSize: 8192 });

// Set a safe limit for header count (e.g., 2000)
httpServer.maxHeadersCount = 2000;

const io = new Server(httpServer, {
    cors: {
        origin: "http://localhost:5173",
        methods: ["GET", "POST"],
        credentials: true
    }
});

// Start listening
httpServer.listen(8081, () => {
    console.log('WebSocket server listening on port 8081');
});

io.on('connection', (socket) => {
    console.log(`Client connected: ${socket.id}`);

    // Handle errors per socket
    socket.on('error', (err) => {
        console.error('Socket error:', err);
    });

    // Transactions
    socket.on('newTransaction', (params) => {
        socket.in(params.transaction.pair_vcard).emit('newTransaction', params);
        if (params.user.user_type === 'A') {
            socket.in(params.transaction.vcard.phone_number).emit('newTransaction', params);
        }
    });

    // Requests
    socket.on('newRequest', (transaction) => {
        if (transaction.custom_options != null) {
            socket.in(transaction.vcard.phone_number).emit('newRequest', transaction);
        } else {
            socket.in(transaction.pair_vcard).emit('newRequest', transaction);
        }
    });

    socket.on('cancelRequest', (transaction) => {
        socket.in(parseInt(transaction.payment_reference)).emit('cancelRequest');
    });

    // Vcard updates
    socket.on('insertVcard', (vcard) => {
        socket.in('administrator').emit('insertVcard', vcard);
    });

    socket.on('updateVcard', (vcard) => {
        socket.in('administrator').except(vcard.phone_number).emit('updateVcard', vcard);
        socket.in(vcard.phone_number).emit('updateVcard', vcard);
    });

    // User login/logout
    socket.on('loggedIn', (user) => {
        socket.join(user.id);
        if (user.user_type === 'A') socket.join('administrator');
    });

    socket.on('loggedOut', (user) => {
        socket.leave(user.id);
        socket.leave('administrator');
    });

    socket.on('disconnect', () => {
        console.log(`Client disconnected: ${socket.id}`);
    });
});
