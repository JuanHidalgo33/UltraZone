document.addEventListener('DOMContentLoaded', function(){
  const popup = document.getElementById('popup');
  if (popup){
    popup.style.display = 'flex';
    setTimeout(() => {
      window.location.href = 'login.php';
    }, 2000);
  }
});

