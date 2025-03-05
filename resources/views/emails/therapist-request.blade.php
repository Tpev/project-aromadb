<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvelle Demande d'Information</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f9fafb; font-family: Arial, sans-serif;">

    <!-- Full-Width Background Table -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f9fafb;">
        <tr>
            <td align="center" style="padding: 30px 10px;">
                <!-- Container Table -->
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 6px; overflow: hidden;">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" style="background-color: #647a0b; padding: 20px;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">
                                Nouvelle Demande d'Information
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 20px; color: #333333; font-size: 16px; line-height: 1.5;">
                            
                            <!-- Additional Sentence -->
                            <p style="margin: 0 0 20px;">
                                Cette demande d'information provient d'un utilisateur visitant votre page thérapeute sur <strong>AromaMade</strong>.
                            </p>

                            <p style="margin: 0 0 10px;">
                                <strong>Prénom :</strong> {{ $firstName }}
                            </p>

                            <p style="margin: 0 0 10px;">
                                <strong>Nom :</strong> {{ $lastName }}
                            </p>

                            <p style="margin: 0 0 10px;">
                                <strong>Email :</strong> {{ $email }}
                            </p>

                            @if($phone)
                                <p style="margin: 0 0 10px;">
                                    <strong>Téléphone :</strong> {{ $phone }}
                                </p>
                            @endif

                            <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">

                            <p style="margin: 0 0 5px;">
                                <strong>Message :</strong>
                            </p>
                            <p style="margin: 0;">
                                {{ $messageContent }}
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background-color: #f9fafb; padding: 15px;">
                            <p style="margin: 0; font-size: 14px; color: #854f38;">
                                &copy; {{ date('Y') }}. Tous droits réservés.
                            </p>
                        </td>
                    </tr>

                </table> <!-- End Container Table -->
            </td>
        </tr>
    </table> <!-- End Full-Width Background Table -->

</body>
</html>
