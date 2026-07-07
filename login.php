<?php
session_start();

// AUTO REDIRECT LOCALHOST → 127.0.0.1
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    header("Location: http://127.0.0.1/sugardips/login.php");
    exit;
}

// IF ALREADY LOGGED IN
if (isset($_SESSION['user_id'])) {
    header('Location: menu.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login — Sugar Dips</title>

    <link rel="stylesheet" href="style.css">

    <style>
        #recaptcha-container {
            margin-top: 0.7rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: center;
        }

        .error-msg {
            color: #ff3366;
            font-size: 0.82rem;
            margin-bottom: 0.7rem;
            text-align: center;
        }

        .hint-text {
            text-align: center;
            color: #777;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>

<div class="login-page">
    <div class="login-card">

        <img src="assets/logo.png" alt="Sugar Dips">

        <h1 class="login-title">
            Welcome to Sugar Dips
        </h1>

        <p class="login-sub">
            Login to place your order 🍫
        </p>

        <!-- PHONE STEP -->
        <div id="step-phone">

            <div class="form-group">
                <label class="form-label">
                    PHONE NUMBER
                </label>

                <div class="phone-row">
                    <span class="phone-prefix">
                        +91
                    </span>

                    <input
                        type="tel"
                        id="phone"
                        class="form-input"
                        maxlength="10"
                        placeholder="9876543210">
                </div>
            </div>

            <!-- RECAPTCHA -->
            <div id="recaptcha-container"></div>

            <!-- ERROR -->
            <div id="phone-error" class="error-msg" style="display:none;"></div>

            <!-- BUTTON -->
            <button
                id="send-btn"
                class="btn-pink full-btn"
                onclick="handleSendOtp()">

                Send OTP →
            </button>
        </div>

        <!-- OTP STEP -->
        <div id="step-otp" style="display:none;">

            <p id="otp-hint" class="hint-text"></p>

            <div class="form-group">
                <label class="form-label">
                    ENTER OTP
                </label>

                <input
                    type="tel"
                    id="otp"
                    class="form-input otp-input"
                    maxlength="6"
                    placeholder="• • • • • •">
            </div>

            <div id="otp-error" class="error-msg" style="display:none;"></div>

            <button
                class="btn-pink full-btn"
                onclick="handleVerifyOtp()">

                Verify OTP →
            </button>

            <button
                class="back-link"
                onclick="goBack()">

                ← Change number
            </button>
        </div>

        <!-- NAME STEP -->
        <div id="step-name" style="display:none;">

            <p class="hint-text">
                Welcome! What should we call you? 🍫
            </p>

            <div class="form-group">
                <label class="form-label">
                    YOUR NAME
                </label>

                <input
                    type="text"
                    id="name"
                    class="form-input"
                    placeholder="e.g. Safoora">
            </div>

            <div id="name-error" class="error-msg" style="display:none;"></div>

            <button
                class="btn-pink full-btn"
                onclick="saveName()">

                Let's Go! 🎉
            </button>
        </div>

    </div>
</div>

<!-- FIREBASE -->

<script type="module">

import { initializeApp }
from "https://www.gstatic.com/firebasejs/9.23.0/firebase-app.js";

import {
    getAuth,
    RecaptchaVerifier,
    signInWithPhoneNumber
}
from "https://www.gstatic.com/firebasejs/9.23.0/firebase-auth.js";

// FIREBASE CONFIG

const firebaseConfig = {
    apiKey: "AIzaSyBNLOeLfXnxgiUASHvYpc_2yfwLYI-bbvs",
    authDomain: "sugar-dips-cd1d7.firebaseapp.com",
    projectId: "sugar-dips-cd1d7",
    storageBucket: "sugar-dips-cd1d7.appspot.com",
    messagingSenderId: "515629087848",
    appId: "1:515629087848:web:40f0d47c14385c8fa94fdd"
};

// INIT

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

let confirmationResult;

// RECAPTCHA

window.setupRecaptcha = function () {

    if (window.recaptchaVerifier) {
        return;
    }

    window.recaptchaVerifier = new RecaptchaVerifier(
        "recaptcha-container",
        {
            size: "normal"
        },
        auth
    );

    window.recaptchaVerifier.render();
};

// SEND OTP

window.firebaseSendOtp = async function (phone) {

    try {

        const appVerifier = window.recaptchaVerifier;

        const result = await signInWithPhoneNumber(
            auth,
            "+91" + phone,
            appVerifier
        );

        confirmationResult = result;

        return {
            success: true
        };

    } catch (error) {

        console.error(error);

        return {
            success: false,
            message: error.message
        };
    }
};

// VERIFY OTP

window.firebaseVerifyOtp = async function (otp, phone) {

    try {

        const result = await confirmationResult.confirm(otp);

        const response = await fetch(
            "firebase_login.php",
            {
                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({
                    phone: phone,
                    uid: result.user.uid
                })
            }
        );

        return await response.json();

    } catch (error) {

        console.error(error);

        return {
            success: false,
            message: "Invalid OTP"
        };
    }
};

</script>

<!-- MAIN SCRIPT -->

<script>

let currentPhone = '';

// SEND OTP

async function handleSendOtp() {

    const phone = document
        .getElementById('phone')
        .value
        .trim();

    if (phone.length !== 10) {

        showError(
            'phone-error',
            'Enter valid 10-digit number'
        );

        return;
    }

    currentPhone = phone;

    const btn = document.getElementById('send-btn');

    btn.disabled = true;
    btn.innerHTML = '⏳ Sending...';

    const result = await window.firebaseSendOtp(phone);

    btn.disabled = false;
    btn.innerHTML = 'Send OTP →';

    if (result.success) {

        showStep2('OTP sent to +91 ' + phone);

    } else {

        showError(
            'phone-error',
            result.message
        );
    }
}

// VERIFY OTP

async function handleVerifyOtp() {

    const otp = document
        .getElementById('otp')
        .value
        .trim();

    if (otp.length !== 6) {

        showError(
            'otp-error',
            'Enter valid OTP'
        );

        return;
    }

    const data = await window.firebaseVerifyOtp(
        otp,
        currentPhone
    );

    if (data.success) {

        if (data.isNew) {

            document.getElementById('step-otp').style.display = 'none';

            document.getElementById('step-name').style.display = 'block';

        } else {

            window.location.href = 'menu.php';
        }

    } else {

        showError(
            'otp-error',
            data.message
        );
    }
}

// SAVE NAME

function saveName() {

    const name = document
        .getElementById('name')
        .value
        .trim();

    if (!name) {

        showError(
            'name-error',
            'Enter your name'
        );

        return;
    }

    fetch('save_name.php', {

        method: 'POST',

        headers: {
            'Content-Type': 'application/json'
        },

        body: JSON.stringify({
            phone: currentPhone,
            name: name
        })

    })
    .then(r => r.json())
    .then(data => {

        if (data.success) {
            window.location.href = 'menu.php';
        }
    });
}

// BACK

function goBack() {

    document.getElementById('step-otp').style.display = 'none';

    document.getElementById('step-phone').style.display = 'block';

    document.getElementById('otp').value = '';
}

// STEP 2

function showStep2(text) {

    document.getElementById('step-phone').style.display = 'none';

    document.getElementById('step-otp').style.display = 'block';

    document.getElementById('otp-hint').textContent = text;
}

// ERROR

function showError(id, msg) {

    const el = document.getElementById(id);

    el.textContent = msg;

    el.style.display = 'block';

    setTimeout(() => {
        el.style.display = 'none';
    }, 4000);
}

// LOAD CAPTCHA ON PAGE LOAD

window.onload = function () {
    setupRecaptcha();
};

</script>

</body>
</html>