function findMe() {
  if(navigator.geolocation){
      navigator.geolocation.getCurrentPosition(successCallback, errorCallback,
    {
      timeout : 10000, // 10s
    }
  );
  }

  else{
    alert("Your device could not report your location"); 
  }
}

function successCallback(position){
  var lat = position.coords.latitude;
  var lng = position.coords.longitude;
  window.location.href = "/?lat=" + lat + "&lng=" + lng;
}


function errorCallback(error){
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
