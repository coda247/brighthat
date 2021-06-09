<?php
namespace App\Interfaces;

interface EmailSecretKeyInterface
{
    // Will change it before pushing to production environment!
    const Host = "premium110.web-hosting.com";
    const SMTPAuth = true;
    const Username = "testmail@brandgeko.com";
    const Password = "testmail9876";
    const SMTPSecure = "ssl";
    const Port = 465;
    const EmailFromName = "Brandgeko Limited";
    const PasswordResetSubject = "Reset Passord";
    const RegistrationVerificationSubject = "Verify your email";
    const SiteUrl= "https://brighthat-client.hadronbox.com";
    const LogoUrl= "https://brighthat-client.hadronbox.com/assets/img/logo-light.svg";
    const RegisterVerificationTemplate = "registration_email_template.html";
    const PasswordResetLink = "password_reset_email_template.html";
}