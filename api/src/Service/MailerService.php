<?php
namespace App\Service;

use App\Entity\Organisation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailerService
{
 
    private $translator;
    private $mailer;
    private $urlGenerator;
    private $em;
    private $pwdEncoder;

    public function __construct (MailerInterface $mailer, TranslatorInterface $translator, UrlGeneratorInterface $urlGenerator, EntityManagerInterface $em){
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
        $this->em = $em;
    }

    public function createTemplatedEmail($recipientAddress, $subject, $text, $ctaLabel=null, $ctaLink=null, $greeting=null, $goodbye=null, $footer=null, $template = "email/base.html.twig" ) {
        $email = (new TemplatedEmail())
        ->from(new Address($_SERVER['FROM_ADDRESS'], $_SERVER['FROM_NAME']))
        ->bcc($_SERVER['BCC_ADDRESS'])
        ->to($recipientAddress)
        ->subject($subject)
        ->htmlTemplate("email/base.html.twig");
        if($greeting == null){
            $greeting = $this->translator->trans("emails.anonymousGreeting");
        }
        if($goodbye == null){
            $goodbye = $this->translator->trans("emails.anonymousGoodbye");
        }
        if($footer == null){
            $footer = $this->translator->trans("emails.footer");
        }
        $attr = [
            "SUBJECT" => $subject,
            "TEXT" => $text,
            "GREETING" => $greeting,
            "GOODBYE" => $goodbye,
            "CTA_LABEL" => $ctaLabel,
            "CTA_LINK" => $ctaLink,
            "FOOTER_TEXT" => $footer
        ];
        $email->context($attr);
        return $email;
        /*->embedFromPath($_SERVER['KERNEL_DIR']."/assets/img/logo_falkenberg_coaching.png", "coaching_logo")
        ->embedFromPath($_SERVER['KERNEL_DIR']."/assets/img/logo_440.png", "falkenberg_logo")
        ->embedFromPath($_SERVER['KERNEL_DIR']."/assets/img/logo_inspiridoo_text.png", "inspiridoo_logo");*/
    }

    public function sendConfirmRegistrationMail(User $user)
    {
        $email = $this->createTemplatedEmail(
            $user->getEmail(), 
            $this->translator->trans("emails.confirmRegistration.subject"),
            $this->translator->trans("emails.confirmRegistration.text"),
            $this->translator->trans("emails.confirmRegistration.cta"),
            $_SERVER['FRONTEND_ADDRESS']."/confirm-account?url=".urlencode($this->urlGenerator->generate("confirm_signup",["id" => $user->getId()], UrlGenerator::ABSOLUTE_PATH))
        );
        $this->mailer->send($email);
    }

    public function sendResetPasswordToken(User $user)
    {
        $token = sha1(random_bytes(10));
        $user->setResetPasswordToken($token);
        $this->em->persist($user);
        $this->em->flush();

        $email = $this->createTemplatedEmail(
            $user->getEmail(), 
            $this->translator->trans("emails.resetPassword.subject"),
            $this->translator->trans("emails.resetPassword.text"),
            $this->translator->trans("emails.resetPassword.cta"),
            $_SERVER['FRONTEND_ADDRESS']."/set-new-password?id=".$user->getId()."&token=".$user->getResetPasswordToken()
        );
        $this->mailer->send($email);
    }

    public function sendInviteToCreateNewOrganisationEmail(User $user)
    {
        $token = sha1(random_bytes(10));
        $user->setResetPasswordToken($token);
        $this->em->persist($user);
        $this->em->flush();

        $email = $this->createTemplatedEmail(
            $user->getEmail(),
            $this->translator->trans("emails.newOrganisation.subject"),
            $this->translator->trans("emails.newOrganisation.text"),
            $this->translator->trans("emails.newOrganisation.cta"),
            $_SERVER['FRONTEND_ADDRESS'] . "/set-new-password?id=" . $user->getId() . "&token=" . $user->getResetPasswordToken(),
            $this->createGreetingForUser($user)
        );
        $this->mailer->send($email);
    }

    public function createGreetingForUser(User $user) {
        return $this->translator->trans("anonymousGreeting");
    }


}