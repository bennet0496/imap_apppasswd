<?php
/*
 * Copyright (c) 2026. Bennet Becker <dev@bennet.cc>
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

$labels['machinetranslated'] = 'Machine Translated using Qwen 3 30B A3B Instruct 2507. In case of errors please open an issue at <a href="https://github.com/bennet0496/imap_apppasswd">bennet0496/imap_apppasswd</a>';

$labels['imap_apppasswd'] = 'Mot de passe d\'application';

$labels['last_used'] = 'Dernière utilisation';
$labels['never_used'] = 'Jamais utilisé';
$labels['last_used_from'] = 'de';
$labels['created'] = 'Créé';
$labels['unnamed_app'] = 'Application sans nom';
$labels['unknown_location'] = 'Emplacement inconnu';
$labels['delete'] = 'Supprimer';
$labels['delete_all'] = 'Supprimer tout';
$labels['confirm_delete_single'] = 'Êtes-vous sûr de vouloir supprimer le mot de passe "$password" ? Cette action ne peut pas être annulée.';
$labels['confirm_delete_all'] = 'Êtes-vous sûr de vouloir supprimer TOUS vos mots de passe d\'application ? Cette action ne peut pas être annulée.';
$labels['add'] = 'Ajouter';
$labels['copied'] = 'Mot de passe copié dans le presse-papiers';
$labels['copy_failed_check_perms'] = 'Échec de la copie du mot de passe, vérifiez les permissions du navigateur';
$labels['edit'] = 'Modifier';
$labels['done'] = 'Terminé';
$labels['ok'] = 'OK';

$labels['no_passwords'] = 'Aucun mot de passe d\'application n\'a encore été créé. Vous pouvez en créer un ci-dessous en cliquant sur le bouton Ajouter.';

$labels['apppw_deleted_success'] = 'Mot de passe d\'application supprimé avec succès';
$labels['apppw_deleted_error'] = 'Erreur lors de la suppression du mot de passe d\'application, veuillez réessayer plus tard';
$labels['apppw_add_error'] = 'Erreur lors de l\'ajout du mot de passe, veuillez réessayer plus tard';
$labels['apppw_rename_error'] = 'Erreur lors du renommage du mot de passe, veuillez réessayer plus tard';
$labels['apppw_rename_error_comment_is_password'] = 'Vous ne pouvez pas utiliser le mot de passe lui-même comme nom de mot de passe';
$labels['apppw_rename_error_too_long'] = 'Le nom du mot de passe est trop long. Veuillez utiliser $max caractères ou moins !';

$labels['years_ago'] = '$count an(s) il y a';
$labels['months_ago'] = '$count mois(s) il y a';
$labels['days_ago'] = '$count jour(s) il y a';
$labels['hours_ago'] = '$count heure(s) il y a';
$labels['minutes_ago'] = '$count minute(s) il y a';
$labels['just_now'] = 'à l\'instant';

$labels['page_allows_password_creation_for_clients'] = <<<EOT
Cette page permet de générer des mots de passe d'application pour vos clients de messagerie. Après avoir créé un mot de passe, saisissez-le ou copiez-le dans votre application de messagerie. Le mot de passe ne sera affiché qu'une seule fois. Cependant, vous n'êtes ni censé ni obligé de vous en souvenir ou de le noter. Si vous souhaitez ajouter un client supplémentaire, générez simplement un nouveau mot de passe ; vous pouvez en créer autant que nécessaire.
EOT;

$labels['use_the_following_settings'] = 'Utilisez les paramètres suivants pour votre client';
$labels['server'] = 'Serveur';
$labels['port'] = 'Port';
$labels['protocol'] = 'Sécurité de connexion';
$labels['password_method'] = 'Méthode d\'authentification';

$labels['imap_setting'] = 'Paramètres entrants (IMAP)';
$labels['smtp_setting'] = 'Paramètres sortants (SMTP)';

$labels['show_full_history'] = 'Afficher l\'historique d\'accès';
$labels['timestamp'] = 'Horodatage';
$labels['service'] = 'Service';
$labels['src_ip'] = 'IP d\'accès';
$labels['src_rdns'] = 'Nom d\'hôte d\'accès';
$labels['src_loc'] = 'Emplacement d\'accès';
$labels['src_isp'] = 'Fournisseur de service d\'accès';
$labels['history_for'] = 'Historique d\'accès pour $password';
$labels['no_history'] = 'Aucun historique';
$labels['history_from_to_of'] = 'Entrées de journal de $from à $to sur $count';

$labels['account_excluded'] = 'Votre compte est exclu ou exempté de l\'utilisation des mots de passe d\'application';