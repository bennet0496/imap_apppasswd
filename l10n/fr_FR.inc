<?php
/*
 * Copyright (c) 2024. Bennet Becker <dev@bennet.cc>
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */

$labels['imap_apppasswd'] = "Mots de passe spécifiques à l'application";

$labels['last_used'] = "Dernière utilisation";
$labels['never_used'] = "Jamais utilisé";
$labels['last_used_from'] = "de";
$labels['created'] = "créé";
$labels['unnamed_app'] = "Application sans nom";
$labels['unknown_location'] = "Lieu inconnu";
$labels['delete'] = "Supprimer";
$labels['delete_all'] = "Supprimer tout";
$labels['confirm_delete_single'] = "Etes-vous sûr de vouloir supprimer le mot de passe \"%password%\"? Cette action ne peut pas être annulée.";
$labels['confirm_delete_all'] = "Êtes-vous sûr de vouloir supprimer TOUS les mots de passe de votre application? Cette action ne peut pas être annulée.";
$labels['add'] = "Ajouter";
$labels['copied'] = "Mot de passe copié dans le presse-papiers";
$labels['copy_failed_check_perms'] = "Échec de la copie du mot de passe, vérifier les permissions du navigateur";
$labels['edit'] = "Editer";
$labels['done'] = "Finition";
$labels['ok'] = "D'accord";

$labels['no_passwords'] = "Aucun mot de passe d'application n'a encore été créé. Vous pouvez en créer un ci-dessous en cliquant sur le bouton Ajouter.";

$labels['apppw_deleted_success'] = "Mot de passe de l'application supprimé avec succès";
$labels['apppw_deleted_error'] = "Erreur de suppression du mot de passe de l'application, veuillez réessayer plus tard";
$labels['apppw_add_error'] = "Erreur lors de l'ajout du mot de passe, veuillez réessayer plus tard";
$labels['apppw_rename_error'] = "Erreur de renommage du mot de passe, veuillez réessayer plus tard";

$labels['years_ago'] = "il y a %s an(s)";
$labels['months_ago'] = "il y a %s mois";
$labels['days_ago'] = "il y a %s jour(s)";
$labels['hours_ago'] = "il y a %s heure";
$labels['minutes_ago'] = "il y a %s minute(s)";
$labels['just_now'] = "à l'instant";

$labels['page_allows_password_creation_for_clients'] = <<<EOT
Cette page permet de créer des mots de passe d'application pour vos clients Mail. Après avoir créé un mot de passe, saisissez-le ou copiez-le dans votre 
demande de courrier électronique. Le mot de passe ne sera affiché qu'une seule fois. Cependant, vous n'êtes ni censé ni obligé de vous souvenir 
ou notez le mot de passe. Si vous souhaitez ajouter un client supplémentaire, créez simplement un nouveau mot de passe ; vous pouvez créer comme 
autant que vous le souhaitez.
EOT;

$labels['use_the_following_settings'] = "Utilisez les paramètres suivants pour votre application";
$labels['server'] = "Serveur";
$labels['port'] = "Port";
$labels['protocol'] = "Sécurité des connexions";
$labels['password_method'] = "Méthode d'authentification";

$labels['imap_setting'] = "Paramètres des messages entrants (IMAP)";
$labels['smtp_setting'] = "Paramètres des messages sortants (SMTP)";

$labels["show_full_history"] = "Afficher l'historique des accès";
$labels["timestamp"] = "Horodatage";
$labels["service"] = "Service";
$labels["src_ip"] = "Accès IP";
$labels["src_rdns"] = "Nom d'hôte de l'accès";
$labels["src_loc"] = "Lieu d'accès";
$labels["src_isp"] = "Fournisseur d'accès";
$labels["history_for"] = 'Historique des accès pour $password';
$labels["no_history"] = "Pas d'entrées dans l'historique";
$labels["history_from_to_of"] = 'Entrées de logs $from à $to de $count';

$labels["account_excluded"] = "Votre compte est exclu ou exempté de l'utilisation des mots de passe d'application";