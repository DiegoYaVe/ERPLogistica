/* if('serviceWorker' in navigator){
  window.addEventListener('load',()=>{
    navigator.serviceWorker.register('firebase-messaging-sw-fabrica.js');
    //console.log("HOLA22");
  });
}
 */

if ('serviceWorker' in navigator) {
navigator.serviceWorker.register('/fabrica/firebase-messaging-sw-fabrica.js')
  .then(function(registration) {
    console.log('Registration successful, scope is:', registration.scope);
  }).catch(function(err) {
    console.log('Service worker registration failed, error:', err);
  });
}

console.log(navigator);
//console.log("HOLA");

importScripts('https://www.gstatic.com/firebasejs/8.4.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.4.1/firebase-messaging.js');

const firebaseConfig = {
  apiKey: "AIzaSyBOVITpI91fki-UDP1_Sigm4DpngyHVhAw",
  authDomain: "notificaciones-fabricainfla.firebaseapp.com",
  projectId: "notificaciones-fabricainfla",
  storageBucket: "notificaciones-fabricainfla.appspot.com",
  messagingSenderId: "987817488194",
  appId: "1:987817488194:web:51ecc5eb8d95fce2f7e2db",
  measurementId: "G-LYVCNDD2QR"
};

  // Initialize Firebase
  firebase.initializeApp(firebaseConfig);
  const messaging1 = firebase.messaging();


messaging1.onBackgroundMessage(function(payload) {
  console.log('[/fabrica/firebase-messaging-sw-fabrica.js] Received background message ', payload);

  firebase.messaging().onBackgroundMessage(registration[0]);
  const title = payload.notification.title;
  const options = payload.notification;
  self.registration.showNotification(title,
      options);
  });






 /*  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/fabrica/firebase-messaging-sw-fabrica.js')
        .then(function(registration) {
          console.log('Service worker registered with scope:', registration.scope);
        }).catch(function(err) {
          console.log('Service worker registration failed, error:', err);
        });
    });
  }
  
  importScripts('https://www.gstatic.com/firebasejs/8.4.1/firebase-app.js');
  importScripts('https://www.gstatic.com/firebasejs/8.4.1/firebase-messaging.js');
  
  const firebaseConfig = {
    apiKey: "AIzaSyBOVITpI91fki-UDP1_Sigm4DpngyHVhAw",
    authDomain: "notificaciones-fabricainfla.firebaseapp.com",
    projectId: "notificaciones-fabricainfla",
    storageBucket: "notificaciones-fabricainfla.appspot.com",
    messagingSenderId: "987817488194",
    appId: "1:987817488194:web:51ecc5eb8d95fce2f7e2db",
    measurementId: "G-LYVCNDD2QR"
  };
  
  firebase.initializeApp(firebaseConfig);
  const messaging = firebase.messaging();
  
  messaging.onBackgroundMessage(function(payload) {
    console.log('Received background message:', payload);
  
    const title = payload.notification.title;
    const options = payload.notification;
    self.registration.showNotification(title,
        options);
  }); */
  

