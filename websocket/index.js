const httpServer = require('http').createServer()
const io = require("socket.io")(httpServer, {
    cors: {
        // The origin is the same as the Vue app domain. Change if necessary
        origin: "http://localhost:5173",
        methods: ["GET", "POST"],
        credentials: true
    }
})
httpServer.listen(8080, () => {
    console.log('listening on *:8080')
})
io.on('connection', (socket) => {
    socket.on('newTransaction', (params) => {
        socket.in(params.transaction.pair_vcard).emit('newTransaction', params)
        if (params.user.user_type == 'A'){
            socket.in(params.transaction.vcard.phone_number).emit('newTransaction', params)
        }
    })
    socket.on('newRequest', (transaction) => {
        if (transaction.custom_options != null){
            socket.in(transaction.vcard.phone_number).emit('newRequest', transaction)
        }else{
            socket.in(transaction.pair_vcard).emit('newRequest', transaction)
        }
    })
    socket.on('cancelRequest', function (transaction) {
        socket.in(parseInt(transaction.payment_reference)).emit('cancelRequest')
    })
    socket.on('insertVcard', function (vcard) {
        socket.in('administrator').emit('insertVcard', vcard)
    })
    socket.on('updateVcard', function (vcard) {
        socket.in('administrator').except(vcard.phone_number).emit('updateVcard', vcard)
        socket.in(vcard.phone_number).emit('updateVcard', vcard)
    })
    socket.on('loggedIn', function (user) {
        socket.join(user.id)
        if (user.user_type == 'A') {
            socket.join('administrator')
        }
    })
    socket.on('loggedOut', function (user) {
        socket.leave(user.id)
        socket.leave('administrator')
    })
})