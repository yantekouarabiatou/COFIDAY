<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vérification OTP</title>

    <!-- Fonts & CSS -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/css/auth.css', 'resources/js/app.js'])
        <!-- === CSS IDENTIQUE À LA PAGE LOGIN === -->
    <style>
        :root {
            --primary-color: #1A237E;
            --primary-light: #3949AB;
            --accent-color: #304FFE;
            --bg-gradient-start: #ffffff;
            --bg-gradient-end: #e1dde5;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--bg-gradient-start), var(--bg-gradient-end));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 1100px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
            display: flex;
            overflow: hidden;
            min-height: 600px;
        }

        /* LEFT */
        .login-left {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            padding: 60px 40px;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .logo-container img {
            max-width: 200px;
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 40px;
        }

        .welcome-text h1 {
            font-size: 2.3rem;
            font-weight: 700;
        }

        .welcome-text p {
            opacity: .9;
            text-align: center;
        }

        .illustration i {
            font-size: 110px;
            opacity: .3;
            margin-top: 40px;
        }

        /* RIGHT */
        .login-right {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header h2 {
            color: var(--primary-color);
            font-weight: 700;
        }

        .otp-field {
            width: 55px;
            height: 55px;
            font-size: 1.8rem;
            text-align: center;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
        }

        .otp-field:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(48,79,254,.15);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: #fff;
            padding: 15px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            width: 100%;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #999;
            font-size: .85rem;
        }

        .illustration {
            position: relative;
            z-index: 2;
            margin-top: 40px;
        }

        .illustration i {
            font-size: 120px;
            opacity: 0.3;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        @media(max-width: 968px){
            .login-container { flex-direction: column; }
        }
    </style>
</head>

<body>

<div class="login-container">

    <!-- LEFT -->
    <div class="login-left auth-left">
        <div class="logo-container">
            <img src="{{ asset('assets/img/logo_cofima_bon.jpg') }}" alt="COFIMA">
        </div>

        <div class="welcome-text">
            <h1>Sécurité renforcée</h1>
            <p>Veuillez confirmer votre identité pour continuer</p>
        </div>

        <div class="auth-illustration">
            <i class="fas fa-shield-alt"></i>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="login-right">

        <div class="login-header mb-4">
            <h2>Vérification OTP</h2>
            <p class="text-muted">Entrez le code reçu par email</p>
        </div>

        <!-- STATUS / ERRORS -->
        <x-auth-session-status class="mb-3 text-success" :status="session('status')" />

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('otp.verify') }}">
            @csrf

            <p class="text-muted text-center mb-3">
                Code envoyé à <strong>{{ $maskedEmail }}</strong>
            </p>

            <p class="text-center">
                <strong id="expire-label">Expire dans :</strong>
                <span id="timer" class="text-danger fw-bold"></span>
            </p>

            <div class="d-flex justify-content-center gap-3 my-4">
                @for ($i = 0; $i < 6; $i++)
                    <input type="text" name="otp_code[]" maxlength="1"
                           class="otp-field otp-field"
                           required>
                @endfor
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-check-circle me-2"></i>
                Vérifier le code
            </button>
        </form>

        <div class="text-center mt-4">
            <small class="text-muted">Vous n'avez rien reçu ?</small><br>
            <a href="#" id="resend-btn" class="fw-bold text-primary">Renvoyer le code</a>
            <div id="resend-message" class="text-success mt-2" style="display:none;">
                Code renvoyé ✔
            </div>
        </div>

        <div class="footer">
            COFIMA © {{ date('Y') }} — Tous droits réservés
        </div>

    </div>
</div>

<script>
const inputs = document.querySelectorAll('.otp-field');
inputs[0].focus();

inputs.forEach((input, index) => {
    input.addEventListener('input', (e) => {
        const value = input.value;
        if (value.length > 1) {
            const chars = value.split('');
            chars.forEach((char, i) => {
                if (inputs[index + i]) inputs[index + i].value = char;
            });
            const last = index + value.length - 1;
            if (inputs[last]) inputs[last].focus();
            return;
        }
        if (value && index < inputs.length - 1) inputs[index + 1].focus();
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === "Backspace" && input.value === "" && index > 0) {
            inputs[index - 1].focus();
        }
    });
});

// Coller un OTP complet
inputs.forEach((input, index) => {
    input.addEventListener('paste', (e) => {
        e.preventDefault();
        const pasteData = (e.clipboardData || window.clipboardData).getData('text');
        const digits = pasteData.replace(/\D/g, '').split(''); // ne garder que les chiffres
        digits.forEach((digit, i) => {
            if (inputs[index + i]) inputs[index + i].value = digit;
        });
        const lastIndex = index + digits.length - 1;
        if (inputs[lastIndex]) inputs[lastIndex].focus();
    });
});


let time = {{ $remainingSeconds }};
const timerElement = document.getElementById("timer");
const expireLabel = document.getElementById("expire-label");
const resendBtn = document.getElementById("resend-btn");
const verifyBtn = document.querySelector("button[type='submit']");
const resendMsg = document.getElementById("resend-message");

function disableResendBtn() {
    resendBtn.classList.add("disabled");
    resendBtn.style.pointerEvents = "none";
    resendBtn.style.opacity = "0.5";
}
function enableResendBtn() {
    resendBtn.classList.remove("disabled");
    resendBtn.style.pointerEvents = "auto";
    resendBtn.style.opacity = "1";
}

function updateTimer() {
    if (time > 0) {
        const minutes = String(Math.floor(time / 60)).padStart(2, '0');
        const seconds = String(time % 60).padStart(2, '0');
        timerElement.textContent = `${minutes}:${seconds}`;
        time--;
        return;
    }

    // Quand le code est expiré
    timerElement.textContent = "Code expiré";
    expireLabel.style.display = "none";

    timerElement.classList.add("text-danger");

    verifyBtn.disabled = true;
    verifyBtn.innerText = "Code expiré";
    verifyBtn.style.opacity = "0.6";
    verifyBtn.style.cursor = "not-allowed";

    enableResendBtn();
    clearInterval(interval);
}

disableResendBtn();
const interval = setInterval(updateTimer, 1000);

resendBtn.addEventListener('click', function(e) {
    e.preventDefault();
    if (resendBtn.classList.contains("disabled")) return;

    resendBtn.textContent = "Renvoi...";
    resendBtn.style.pointerEvents = "none";

    fetch("{{ route('otp.resend') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        }
    })
    .then(response => response.json())
    .then(data => {
        time = data.remainingSeconds;
        timerElement.classList.remove("text-danger");
        verifyBtn.disabled = false;
        verifyBtn.innerText = "Vérifier le code";
        verifyBtn.style.opacity = "1";
        verifyBtn.style.cursor = "pointer";
        expireLabel.style.display = "inline"; // remettre le label
        resendBtn.textContent = "Renvoyer le code";

        clearInterval(interval);
        setInterval(updateTimer, 1000);

        disableResendBtn();
        resendMsg.style.display = "block";
        setTimeout(() => resendMsg.style.display = "none", 2500);
    });
});
</script>

</body>
</html>
