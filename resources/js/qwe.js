console.log(111)
window.Echo.channel('qwerty')
    .listen('.qwerty',data => {
        console.log(data)
    })

