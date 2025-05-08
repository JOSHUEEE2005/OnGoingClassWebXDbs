<?php
session_start();
require_once('classes/database.php');
require_once('classes/functions.php');

$con = new database();
$sweetAlertConfig = "";

if (isset($_POST['multisave'])) {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $birthday = $_POST['birthday'];
    $sex = $_POST['sex'];
    $phone = $_POST['phone'];

    // Check if username or email already exists
    if ($con->checkUserExists($email, $username)) {
        $_SESSION['error'] = "Email or username already taken.";
    } else {
        $profile_picture_path = handleFileUpload($_FILES["profile_picture"]);
        if ($profile_picture_path === false) {
            $_SESSION['error'] = "Sorry, there was an error uploading your file.";
        } else {
            $userId = $con->signupUser($firstname, $lastname, $birthday, $sex, $phone, $email, $username, $password, $profile_picture_path);
            if ($userId) {
                $street = $_POST['user_street'];
                $barangay = $_POST['user_barangay'];
                $city = $_POST['user_city'];
                $province = $_POST['user_province'];

                if ($con->insertAddress($userId, $street, $barangay, $city, $province)) {
                    $sweetAlertConfig = "<script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Registration Successful',
                            text: 'Your account has been created successfully',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'login.php';
                            }
                        });
                    </script>";
                } else {
                    $_SESSION['error'] = "An error occurred while inserting the address.";
                }
            } else {
                $_SESSION['error'] = "Sorry, there was an error signing up.";
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="./bootstrap-4.5.3-dist/css/bootstrap.css">
  <link rel="stylesheet" href="./bootstrap-5.3.3-dist/css/bootstrap.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <title>LMS | Registration</title>
  <style>
    .form-step {
      display: none;
    }
    .form-step-active {
      display: block;
    }
  </style>
</head>
<body>
<?php
if (!empty($sweetAlertConfig)) {
    echo $sweetAlertConfig;
}
if (isset($_SESSION['error'])) {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '" . addslashes($_SESSION['error']) . "',
            confirmButtonText: 'OK'
        });
    </script>";
    unset($_SESSION['error']);
}
?>
<div class="container custom-container rounded-3 shadow my-5 p-3 px-5">
  <h3 class="text-center mt-4">Registration Form</h3>
  <form method="post" action="" enctype="multipart/form-data" novalidate>
    <div class="form-step form-step-active" id="step-1">
      <div class="card mt-4">
        <div class="card-header bg-info text-white">Account Information</div>
        <div class="card-body">
          <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" class="form-control" name="username" id="username" placeholder="Enter username" required>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Please enter a valid username.</div>
          </div>
          <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Please enter a valid email.</div>
          </div>
          <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" class="form-control" name="password" placeholder="Enter password" required>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one special character.</div>
          </div>
          <div class="form-group">
            <label for="confirmPassword">Confirm Password:</label>
            <input type="password" class="form-control" name="confirmPassword" placeholder="Re-enter your password" required>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Please confirm your password.</div>
          </div>
        </div>
      </div>
      <button type="button" id="nextButton" class="btn btn-primary mt-3" onclick="nextStep()">Next</button>
    </div>

    <div class="form-step" id="step-2">
      <div class="card mt-4">
        <div class="card-header bg-info text-white">Personal Information</div>
        <div class="card-body">
          <div class="form-row">
            <div class="form-group col-md-6 col-sm-12">
              <label for="firstName">First Name:</label>
              <input type="text" class="form-control" name="firstname" placeholder="Enter first name" required>
              <div class="valid-feedback">Looks good!</div>
              <div class="invalid-feedback">Please enter a valid first name.</div>
            </div>
            <div class="form-group col-md-6 col-sm-12">
              <label for="lastName">Last Name:</label>
              <input type="text" class="form-control" name="lastname" placeholder="Enter last name" required>
              <div class="valid-feedback">Looks good!</div>
              <div class="invalid-feedback">Please enter a valid last name.</div>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="birthday">Birthday:</label>
              <input type="date" class="form-control" name="birthday" id="birthday" required>
              <div class="valid-feedback">Great!</div>
              <div class="invalid-feedback">Please enter a valid birthday.</div>
            </div>
            <div class="form-group col-md-6">
              <label for="sex">Sex:</label>
              <select class="form-control" name="sex" required>
                <option selected disabled value="">Select Sex</option>
                <option>Male</option>
                <option>Female</option>
              </select>
              <div class="valid-feedback">Looks good!</div>
              <div class="invalid-feedback">Please select a sex.</div>
            </div>
            <div class="form-group col-md-6">
              <label for="phone">Phone Number:</label>
              <input type="text" class="form-control" name="phone" placeholder="Enter phone number" required>
              <div class="valid-feedback">Looks good!</div>
              <div class="invalid-feedback">Please enter a valid phone number.</div>
            </div>
            <div class="form-group col-md-6">
              <label for="profile_picture">Profile Picture:</label>
              <input type="file" class="form-control" name="profile_picture" accept="image/*" required>
              <div class="valid-feedback">Looks good!</div>
              <div class="invalid-feedback">Please upload a valid image.</div>
            </div>
          </div>
        </div>
      </div>
      <button type="button" class="btn btn-secondary mt-3" onclick="prevStep()">Previous</button>
      <button type="button" class="btn btn-primary mt-3" onclick="nextStep()">Next</button>
    </div>

    <div class="form-step" id="step-3">
      <div class="card mt-4">
        <div class="card-header bg-info text-white">Address Information</div>
        <div class="card-body">
          <div class="form-group">
            <label class="form-label">Region<span class="text-danger"> *</span></label>
            <select name="user_region" class="form-control form-control-md" id="region"></select>
            <input type="hidden" class="form-control form-control-md" name="user_region_text" id="region-text">
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Please select a region.</div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label class="form-label">Province<span class="text-danger"> *</span></label>
              <select name="user_province" class="form-control form-control-md" id="province"></select>
              <input type="hidden" class="form-control form-control-md" name="user_province" id="province-text" required>
              <div class="valid-feedback">Looks good!</div>
              <div class="invalid-feedback">Please select your province.</div>
            </div>
            <div class="form-group col-md-6">
              <label class="form-label">City / Municipality<span class="text-danger"> *</span></label>
              <select name="user_city" class="form-control form-control-md" id="city"></select>
              <input type="hidden" class="form-control form-control-md" name="user_city" id="city-text" required>
              <div class="valid-feedback">Looks good!</div>
              <div class="invalid-feedback">Please select your city/municipality.</div>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Barangay<span class="text-danger"> *</span></label>
            <select name="user_barangay" class="form-control form-control-md" id="barangay"></select>
            <input type="hidden" class="form-control form-control-md" name="user_barangay" id="barangay-text" required>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Please select your barangay.</div>
          </div>
          <div class="form-group">
            <label class="form-label">Street <span class="text-danger"> *</span></label>
            <input type="text" class="form-control form-control-md" name="user_street" id="street-text" required>
            <div class="valid-feedback">Looks good!</div>
            <div class="invalid-feedback">Please enter your street.</div>
          </div>
        </div>
      </div>
      <button type="button" class="btn btn-secondary mt-3" onclick="prevStep()">Previous</button>
      <button type="submit" name="multisave" class="btn btn-primary mt-3">Sign Up</button>
      <a class="btn btn-outline-danger mt-3" href="index.php">Go Back</a>
    </div>
  </form>
</div>
<script src="./bootstrap-5.3.3-dist/js/bootstrap.js"></script>
<script src="ph-address-selector.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");
  const birthdayInput = document.getElementById("birthday");
  const steps = document.querySelectorAll(".form-step");
  let currentStep = 0;

  const today = new Date().toISOString().split('T')[0];
  birthdayInput.setAttribute('max', today);

  const inputs = form.querySelectorAll("input, select");
  inputs.forEach(input => {
    input.addEventListener("input", () => validateInput(input));
    input.addEventListener("change", () => validateInput(input));
  });

  form.addEventListener("submit", (event) => {
    if (!validateStep(currentStep)) {
      event.preventDefault();
      event.stopPropagation();
    }
    form.classList.add("was-validated");
  }, false);

  window.nextStep = () => {
    if (validateStep(currentStep)) {
      steps[currentStep].classList.remove("form-step-active");
      currentStep++;
      steps[currentStep].classList.add("form-step-active");
    }
  };

  window.prevStep = () => {
    steps[currentStep].classList.remove("form-step-active");
    currentStep--;
    steps[currentStep].classList.add("form-step-active");
  };

  function validateStep(step) {
    let valid = true;
    const stepInputs = steps[step].querySelectorAll("input, select");
    stepInputs.forEach(input => {
      if (!validateInput(input)) {
        valid = false;
      }
    });
    return valid;
  }

  function validateInput(input) {
    if (input.name === 'password') {
      return validatePassword(input);
    } else if (input.name === 'confirmPassword') {
      return validateConfirmPassword(input);
    } else {
      if (input.checkValidity()) {
        input.classList.remove("is-invalid");
        input.classList.add("is-valid");
        return true;
      } else {
        input.classList.remove("is-valid");
        input.classList.add("is-invalid");
        return false;
      }
    }
  }

  function validatePassword(passwordInput) {
    const password = passwordInput.value;
    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
    if (regex.test(password)) {
      passwordInput.classList.remove("is-invalid");
      passwordInput.classList.add("is-valid");
      return true;
    } else {
      passwordInput.classList.remove("is-valid");
      passwordInput.classList.add("is-invalid");
      return false;
    }
  }

  function validateConfirmPassword(confirmPasswordInput) {
    const passwordInput = form.querySelector("input[name='password']");
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    if (password === confirmPassword && password !== '') {
      confirmPasswordInput.classList.remove("is-invalid");
      confirmPasswordInput.classList.add("is-valid");
      return true;
    } else {
      confirmPasswordInput.classList.remove("is-valid");
      confirmPasswordInput.classList.add("is-invalid");
      return false;
    }
  }
});
</script>
</body>
</html>