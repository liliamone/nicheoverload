# Instructions pour Activer le Debug WordPress

## Méthode 1 : Modifier wp-config.php

Ajoutez ces lignes dans votre fichier `wp-config.php` (avant la ligne `/* That's all, stop editing! */`) :

```php
// Enable Debug Mode
define('WP_DEBUG', true);

// Enable Debug Logging to /wp-content/debug.log
define('WP_DEBUG_LOG', true);

// Disable display of errors on screen
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);

// Log all errors, warnings, and notices
error_reporting(E_ALL);
```

## Méthode 2 : Via Plugin (plus facile)

Si vous avez accès au dashboard WordPress :
1. Installez le plugin "WP Debugging"
2. Activez-le
3. Allez dans Réglages → WP Debugging
4. Cochez "Enable WP_DEBUG"
5. Cochez "Enable WP_DEBUG_LOG"

## Localisation des Logs

Après activation, les logs seront dans :
- `wp-content/debug.log` (WordPress standard)
- `wp-content/plugins/independent-niche/logs/independent-niche.log` (Plugin spécifique)

## Consulter les Logs

### En ligne de commande :
```bash
# Voir les 50 dernières lignes
tail -50 wp-content/debug.log

# Suivre en temps réel
tail -f wp-content/debug.log

# Filtrer par "Independent Niche"
grep "Independent Niche" wp-content/debug.log
```

### Via FTP/cPanel :
1. Connectez-vous à votre serveur
2. Naviguez vers `wp-content/`
3. Téléchargez `debug.log`
4. Ouvrez avec un éditeur de texte

## Désactiver le Debug (après diagnostic)

Remplacez dans wp-config.php :
```php
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
```
