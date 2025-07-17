
var seconds = 0;
var minutes = 0;
var hours = 0;
var timer;
var running = false;

function startTimerTW() {
    running = true;
    timer = setInterval(() => {
        if (running) {
            seconds++;
            if (seconds === 60) {
                seconds = 0;
                minutes++;
                if (minutes === 60) {
                    minutes = 0;
                    hours++;
                }
            }
            postMessage({ hours, minutes, seconds, running});
        }
    }, 1000);
}

function pauseTimerTW() {
    running = false;
    clearInterval(timer); // Detener el temporizador actual
}

function stopTimerTW() {
    running = false;
    /* seconds = 0;
    minutes = 0;
    hours = 0; */
    clearInterval(timer); // Detener el temporizador actual
    postMessage({ hours, minutes, seconds, running});
}

self.onmessage = function (event) {
    if (event.data.action === 'start') {
        startTimerTW();
    } else if (event.data.action === 'pause') {
        console.log("Aquí mero");
        pauseTimerTW();
    } else if (event.data.action === 'stop') {
        stopTimerTW();
    } else if (event.data.action === '') {
        console.log("Aquí mero");
        pauseTimerTW();
    } else if (event.data.action === 'setValues') {
        // Accede a los valores enviados
        seconds = event.data.seconds;
        minutes = event.data.minutes;
        hours = event.data.hours;
    }
};
