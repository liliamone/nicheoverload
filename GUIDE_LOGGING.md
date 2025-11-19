# 📋 Guide Complet du Système de Logging - Independent Niche Generator

## 🎯 Vue d'ensemble

Le plugin dispose maintenant d'un système de logging complet qui enregistre **toutes les opérations critiques** dans un fichier dédié.

---

## 📁 Fichiers de Log

### 1. Log Principal du Plugin
**Emplacement :** `wp-content/plugins/independent-niche/logs/independent-niche.log`

Ce fichier contient :
- ✅ Toutes les requêtes DeepSeek API
- ✅ Parsing des réponses JSON
- ✅ Erreurs et exceptions
- ✅ Navigation dans le wizard
- ✅ Génération d'articles
- ✅ Succès et échecs de chaque opération

### 2. Log WordPress Standard
**Emplacement :** `wp-content/debug.log`

Ce fichier contient :
- Toutes les erreurs WordPress
- Erreurs PHP
- Warnings et notices

---

## 🚀 Comment Activer le Logging

### Méthode 1 : Automatique (Recommandée)

Le logging s'active automatiquement si vous avez `WP_DEBUG` activé dans `wp-config.php`.

Ajoutez dans `wp-config.php` (avant `/* That's all, stop editing! */`) :

```php
// Enable Debug Mode
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);
```

### Méthode 2 : Via Code

Ajoutez temporairement dans `functions.php` de votre thème :

```php
// Activer le logging du plugin
\IndependentNiche\application\helpers\Logger::enableDebug();
```

Pour désactiver :
```php
\IndependentNiche\application\helpers\Logger::disableDebug();
```

---

## 📖 Format des Logs

Chaque ligne de log suit ce format :

```
[DATE HEURE] [User:ID] [NIVEAU  ] Message
    Context: {...détails JSON...}
```

### Exemple Réel :

```
[2025-01-19 14:23:45] [User:1] [INFO    ] Attempting to initialize niche from DeepSeek
    Context: {
        "niche": "hiking",
        "language": "English"
    }

[2025-01-19 14:23:47] [User:1] [API     ] API [chat/completions] - Status: SENDING - Sending request to DeepSeek API
    Context: {
        "model": "deepseek-chat",
        "max_tokens": 2000
    }

[2025-01-19 14:23:49] [User:1] [API     ] API [chat/completions] - Status: SUCCESS - API request successful
    Context: {
        "duration_ms": 1847.23,
        "response_size": 1523
    }

[2025-01-19 14:23:49] [User:1] [SUCCESS ] Successfully initialized niche data from DeepSeek
    Context: {
        "recipes_count": 3,
        "keywords_count": 10,
        "remaining_credits": 100
    }
```

---

## 🔍 Types de Messages Log

| Niveau | Description | Exemple |
|--------|-------------|---------|
| **ERROR** | Erreur critique | DeepSeek API Error, JSON Parse Error |
| **WARNING** | Avertissement | Validation échouée, champ manquant |
| **INFO** | Information | Début d'opération, étape du wizard |
| **DEBUG** | Détails techniques | Parsing JSON, contenu de réponse |
| **SUCCESS** | Opération réussie | Niche initialisée, article généré |
| **API** | Requête API | Appel DeepSeek, durée, statut |
| **WIZARD** | Navigation wizard | Étape complétée, validation |

---

## 📥 Comment Récupérer les Logs

### Option 1 : Via FTP/cPanel

1. Connectez-vous à votre serveur (FTP, SFTP, ou cPanel File Manager)
2. Naviguez vers : `wp-content/plugins/independent-niche/logs/`
3. Téléchargez `independent-niche.log`
4. Ouvrez avec un éditeur de texte (Notepad++, VS Code, etc.)

### Option 2 : Via SSH/Terminal

```bash
# Voir les 100 dernières lignes
tail -100 wp-content/plugins/independent-niche/logs/independent-niche.log

# Suivre en temps réel
tail -f wp-content/plugins/independent-niche/logs/independent-niche.log

# Chercher les erreurs
grep "ERROR" wp-content/plugins/independent-niche/logs/independent-niche.log

# Chercher les appels API
grep "API" wp-content/plugins/independent-niche/logs/independent-niche.log

# Chercher pour un mot spécifique (ex: DeepSeek)
grep -i "deepseek" wp-content/plugins/independent-niche/logs/independent-niche.log
```

### Option 3 : Via Plugin WordPress

Installez le plugin "**WP Log Viewer**" depuis le repository WordPress :
1. Extensions → Ajouter
2. Recherchez "WP Log Viewer"
3. Installez et activez
4. Accédez à Outils → Log Viewer
5. Sélectionnez le fichier de log

---

## 🐛 Scénarios de Diagnostic

### Problème 1 : Wizard Bloqué à l'Étape 1

**Cherchez dans les logs :**
```bash
grep -A 5 "initializeNicheFromApi" independent-niche.log
```

**Ce qu'on devrait voir :**
- `INFO: Attempting to initialize niche from DeepSeek`
- `API: Sending request to DeepSeek API`
- `API: SUCCESS` ou `API: FAILED`

**Si vous voyez `API: FAILED`**, regardez le contexte pour l'erreur exacte.

---

### Problème 2 : Génération d'Articles en "Processing"

**Cherchez :**
```bash
grep -i "article" independent-niche.log | tail -50
```

**Vérifiez :**
- Y a-t-il des erreurs `ERROR` ?
- Les recettes sont-elles bien chargées ?
- Le `remaining_credits` est-il > 0 ?

---

### Problème 3 : DeepSeek Retourne une Erreur

**Cherchez :**
```bash
grep "DeepSeek API Error" independent-niche.log
```

**Erreurs communes :**
- `401 Unauthorized` → Clé API invalide
- `429 Too Many Requests` → Limite de taux dépassée
- `500 Internal Server Error` → Problème côté DeepSeek
- `Timeout` → Connexion trop lente

---

### Problème 4 : JSON Parse Error

**Cherchez :**
```bash
grep -A 10 "JSON Parse Error" independent-niche.log
```

**Le log montrera :**
- L'erreur JSON exacte
- Les 500 premiers caractères de la réponse DeepSeek
- Permet de voir si DeepSeek a retourné du markdown au lieu de JSON pur

---

## 📤 Envoyer les Logs pour Support

### Ce que je dois voir :

1. **Les 200 dernières lignes du log :**
```bash
tail -200 wp-content/plugins/independent-niche/logs/independent-niche.log > logs_export.txt
```

2. **Filtré par timestamp (ex: aujourd'hui) :**
```bash
grep "2025-01-19" independent-niche.log > logs_today.txt
```

3. **Seulement les erreurs :**
```bash
grep "ERROR\|FAILED" independent-niche.log > errors_only.txt
```

### Format d'Envoi :

Envoyez-moi le fichier avec :
- **Date et heure** du problème
- **Description** de ce que vous faisiez
- **Comportement attendu** vs **comportement réel**

---

## 🧹 Maintenance des Logs

### Rotation Automatique

Le système fait automatiquement :
- Rotation quand le fichier dépasse 5MB
- Création d'un backup : `independent-niche.log.2025-01-19-143045.bak`
- Conservation des 5 derniers backups
- Suppression automatique des plus anciens

### Effacer Manuellement

```bash
# Vider le log
echo "" > wp-content/plugins/independent-niche/logs/independent-niche.log

# Supprimer tous les logs
rm -f wp-content/plugins/independent-niche/logs/*.log
rm -f wp-content/plugins/independent-niche/logs/*.bak
```

---

## 🔒 Sécurité

### Protection des Logs

Le dossier `logs/` est protégé par :
- Fichier `.htaccess` qui bloque l'accès web
- Les clés API sont masquées dans les logs (seulement 10 premiers caractères)
- Pas d'informations sensibles loggées

### Logs Visibles Via :
✅ FTP/SFTP
✅ SSH
✅ cPanel File Manager
❌ URL directe (bloquée par .htaccess)

---

## 💡 Conseils Pro

### 1. Activer Temporairement

Activez le logging SEULEMENT quand vous diagnostiquez un problème :
- Réduit la taille des fichiers
- Améliore les performances

### 2. Regarder en Temps Réel

Pendant le test, utilisez :
```bash
tail -f wp-content/plugins/independent-niche/logs/independent-niche.log
```

Cela affiche les logs **en temps réel** pendant que vous utilisez le wizard.

### 3. Combiner avec Browser DevTools

- Ouvrez F12 → Console → Network
- Vérifiez les erreurs JavaScript
- Comparez avec les logs serveur

---

## 📞 Support

Si vous rencontrez un problème :

1. **Activez le logging** (WP_DEBUG ou enableDebug())
2. **Reproduisez le problème**
3. **Récupérez les logs** (200 dernières lignes minimum)
4. **Envoyez-moi** avec description détaillée

Je pourrai alors :
- ✅ Voir EXACTEMENT ce qui se passe
- ✅ Identifier la cause racine
- ✅ Proposer un fix rapide

---

**Version du Guide :** 2.3.1
**Dernière mise à jour :** 2025-01-19
