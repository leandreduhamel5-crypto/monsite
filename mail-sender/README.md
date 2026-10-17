# Node.js Mail Sender avec Brevo API

Application pour envoyer des emails en masse via l'API Brevo avec configuration flexible.

## Installation

```bash
npm install
```

## Configuration

Édite `config.json` avec tes paramètres Brevo:

```json
{
  "brevo": {
    "apiKey": "ta-cle-api-brevo"
  },
  "sender": {
    "from": "sender@example.com",
    "fromName": "Ton Nom"
  },
  "email": {
    "object": "Ton sujet ici",
    "templateFile": "letter.html"
  },
  "mailList": "ml.txt",
  "delay": 1000
}
```

### Paramètres

| Paramètre | Description |
|-----------|-------------|
| `brevo.apiKey` | Ta clé API Brevo |
| `sender.from` | Adresse email d'envoi |
| `sender.fromName` | Nom de l'expéditeur |
| `email.object` | Sujet du mail |
| `email.templateFile` | Fichier HTML du template |
| `mailList` | Fichier contenant la liste des emails |
| `delay` | Délai entre chaque envoi (ms) |

## Fichiers

- **`config.json`** - Configuration complète
- **`ml.txt`** - Liste d'emails (un par ligne)
- **`letter.html`** - Template HTML du mail
- **`sender.js`** - Logique d'envoi via API Brevo
- **`test.js`** - Script de test

## Utilisation

### 1. Récupérer ta clé API Brevo

1. Va sur https://app.brevo.com
2. Settings (⚙️) → SMTP & API
3. Copie ta **clé API** (API v3)

### 2. Configurer config.json

```json
"brevo": {
  "apiKey": "xkeysib-1234567890abcdef..."
}
```

### 3. Ajouter tes emails dans ml.txt

```
client1@example.com
client2@example.com
client3@example.com
```

### 4. Envoyer les emails

```bash
npm test
```

Ou en code:
```javascript
const { sendBulkEmails } = require('./sender');
await sendBulkEmails();
```

## Envoyer un email unique

```javascript
const { sendEmail } = require('./sender');

await sendEmail(
  'recipient@example.com',
  'Sujet',
  '<h1>Contenu HTML</h1>'
);
```

## Format ml.txt

```
email1@example.com
email2@example.com
email3@example.com
```

## 🎯 Avantages de l'API vs SMTP

✅ Plus fiable  
✅ Pas de limitation de connexion  
✅ Meilleur suivi  
✅ Plus rapide  

## Où trouver ta clé API Brevo

1. Va sur https://app.brevo.com
2. Settings → SMTP & API
3. Copie la **clé API v3**
