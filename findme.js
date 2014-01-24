function findMe() {
    navigator.geolocation.getCurrentPosition(
    successCallback,
    errorCallback_highAccuracy,
    {maximumAge:0, timeout:5000, enableHighAccuracy: true}
  );
}

function errorCallback_highAccuracy(position) {
        navigator.geolocation.getCurrentPosition(
               successCallback,
               errorCallback_lowAccuracy,
               {maximumAge:0, timeout:10000, enableHighAccuracy: false});
}

function errorCallback_lowAccuracy(error) {
     switch(error.code){
     case error.PERMISSION_DENIED:
         alert("You must enable GPS or permit your browser to access it.");
         break;
     case error.POSITION_UNAVAILABLE:
         alert("Your device could not report your location.");
         break;
     case error.TIMEOUT:
         alert("Your device could not report your location.");
         break;
     case error.UNKNOW_ERROR:
         alert("Your device could not report your location");
         break;
     }

}

function successCallback(position) {
    var lat = position.coords.latitude;
    var lng = position.coords.longitude;
    var acc = position.coords.accuracy;
    window.location.href = "/?lat=" + lat + "&lng=" + lng + "&acc=" + acc ;
}
