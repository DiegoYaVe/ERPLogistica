var seconds = 0;
var minutes = 0;
var timer;
var running = false;

function startTimerTW(totalSeconds) {
    stopTimer();

    running = true;
    seconds = totalSeconds % 60;
    minutes = Math.floor((totalSeconds % 3600) / 60);

    timer = setInterval(() => {
        if (running) {
            if (totalSeconds <= 0) {
                stopTimer();
                postMessage({ minutes, seconds, running });
            } else {
                totalSeconds--;
                seconds = totalSeconds % 60;
                minutes = Math.floor((totalSeconds % 3600) / 60);
                postMessage({ minutes, seconds, running });
            }
        }
    }, 1000);
}

function stopTimer() {
    running = false;
    clearInterval(timer);
}

self.onmessage = function (event) {
    if (event.data.action === 'start') {
        startTimerTW(300);
    } else if (event.data.action === 'stop') {
        stopTimer();
    }
};

