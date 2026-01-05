<!DOCTYPE html>
<html lang="<?php echo $langue ?? 'fr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | ZRSGIMT</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #240046 0%, #3c096c 50%, #ff5400 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            display: flex;
            min-height: 600px;
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #240046 0%, #3c096c 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .logo {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .logo-subtitle {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 40px;
        }

        .feature-list {
            list-style: none;
            text-align: left;
            width: 100%;
            max-width: 350px;
        }

        .feature-item {
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
        }

        .feature-item:last-child {
            border-bottom: none;
        }

        .feature-icon {
            width: 30px;
            height: 30px;
            background: #ff5400;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .login-right {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-title {
            font-size: 32px;
            color: #240046;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .login-subtitle {
            color: #6c757d;
            margin-bottom: 40px;
        }

        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-danger {
            background: #fee;
            color: #dc3545;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            color: #333;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 14px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: #240046;
            box-shadow: 0 0 0 3px rgba(36, 0, 70, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: #240046;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #ff5400;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 84, 0, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .form-footer {
            margin-top: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }

            .login-left {
                padding: 40px 20px;
            }

            .login-right {
                padding: 40px 20px;
            }

            .logo {
                font-size: 36px;
            }

            .login-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div class="logo">ZRSGIMT</div>
            <div class="logo-subtitle">Système de Gestion Intégrée</div>
            <ul class="feature-list">
                <li class="feature-item">
                    <div class="feature-icon">✓</div>
                    <div>Gestion du parc d'engins</div>
                </li>
                <li class="feature-item">
                    <div class="feature-icon">✓</div>
                    <div>Suivi des livraisons</div>
                </li>
                <li class="feature-item">
                    <div class="feature-icon">✓</div>
                    <div>Gestion des stocks (CUMP)</div>
                </li>
                <li class="feature-item">
                    <div class="feature-icon">✓</div>
                    <div>Maintenance préventive</div>
                </li>
                <li class="feature-item">
                    <div class="feature-icon">✓</div>
                    <div>Facturation automatisée</div>
                </li>
                <li class="feature-item">
                    <div class="feature-icon">✓</div>
                    <div>Documents administratifs</div>
                </li>
            </ul>
        </div>

        <div class="login-right">
            <h1 class="login-title">Bienvenue</h1>
            <p class="login-subtitle">Connectez-vous à votre compte</p>

            <?php if (isset($timeout) && $timeout == '1'): ?>
                <div class="alert alert-warning">
                    Votre session a expiré. Veuillez vous reconnecter.
                </div>
            <?php endif; ?>

            <?php if (isset($message) && $message == 'logged_out'): ?>
                <div class="alert alert-success">
                    Vous avez été déconnecté avec succès.
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php
                    switch ($error) {
                        case 'csrf':
                            echo 'Erreur de sécurité. Veuillez réessayer.';
                            break;
                        case 'empty_fields':
                            echo 'Veuillez remplir tous les champs.';
                            break;
                        case 'invalid_email':
                            echo 'Format d\'email invalide.';
                            break;
                        case 'invalid_credentials':
                            echo 'Email ou mot de passe incorrect.';
                            if (isset($_GET['attempts_left'])) {
                                echo '<br>Tentatives restantes : ' . htmlspecialchars($_GET['attempts_left']);
                            }
                            break;
                        case 'blocked':
                            echo 'Votre compte est temporairement bloqué. Réessayez dans 15 minutes.';
                            break;
                        case 'inactive':
                            echo 'Votre compte est désactivé. Contactez l\'administrateur.';
                            break;
                        case 'too_many_attempts':
                            echo 'Trop de tentatives. Votre compte est bloqué pendant 15 minutes.';
                            break;
                        default:
                            echo 'Une erreur est survenue.';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo BASE_URL; ?>/auth/login">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                
                <div class="form-group">
                    <label class="form-label" for="email">Adresse email</label>
                    <input 
                        type="email" 
                        class="form-control" 
                        id="email" 
                        name="email" 
                        placeholder="votre.email@example.com"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Mot de passe</label>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="password" 
                        name="password" 
                        placeholder="Entrez votre mot de passe"
                        required
                    >
                </div>

                <button type="submit" class="btn-login">Se connecter</button>

                <div class="form-footer">
                    <p>&copy; <?php echo date('Y'); ?> ZRSGIMT. Tous droits réservés.</p>
                    <p style="margin-top: 10px; font-size: 12px;">
                        Comptes de démonstration : admin@zrsgimt.com / Admin@2025
                    </p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
