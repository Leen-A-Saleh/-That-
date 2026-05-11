document.querySelector('.card__form').addEventListener('submit', function (e) {
  var password = document.getElementById('password');
  var confirm = document.getElementById('confirm_password');
  var passwordError = document.getElementById('password-error');
  var confirmError = document.getElementById('confirm-error');

  passwordError.textContent = '';
  confirmError.textContent = '';
  password.classList.remove('input-error');
  confirm.classList.remove('input-error');

  var hasError = false;

  if (password.value.length < 8) {
    passwordError.textContent = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
    password.classList.add('input-error');
    hasError = true;
  }

  if (password.value !== confirm.value) {
    confirmError.textContent = 'كلمات المرور غير متطابقة';
    confirm.classList.add('input-error');
    hasError = true;
  }

  if (hasError) {
    e.preventDefault();
  }
});
