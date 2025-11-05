document.addEventListener('DOMContentLoaded', function(){
  const popupLogin = document.getElementById('popup');
  if (popupLogin){
    popupLogin.style.display = 'flex';
    setTimeout(() => {
      window.location.href = "../index.html";
    }, 2000);
  }

  const popupLogout = document.getElementById('popup-logout');
  if (popupLogout){
    popupLogout.style.display = 'flex';
    setTimeout(() => {
      popupLogout.style.display = 'none';
    }, 1800);
  }
});

