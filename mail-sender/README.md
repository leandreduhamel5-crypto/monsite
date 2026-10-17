# Node.js Mail Sender avec Brevo SMTP

Application pour envoyer des emails en masse via Brevo SMTP avec configuration flexible.

## Installation

```bash
npm install
```

## Configuration

Édite `config.json` avec tes paramètres:

```json
{
  "smtp": {
    "host": "smtp-relay.brevo.com",
    "port": 587,
    "secure": false,
    "auth": {
      "user": "ton-email-brevo@example.com",
      "pass": "ta-clé-smtp-brevo"
    }
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
- **`sender.js`** - Logique d'envoi
- **`test.js`** - Script de test

## Utilisation

### Envoyer à la liste (ml.txt) avec template (letter.html)

```bash
npm test
```

Ou en code:
```javascript
const { sendBulkEmails } = require('./sender');
await sendBulkEmails();
```

### Envoyer un email unique

```javascript
const { sendEmail } = require('./sender');

await sendEmail(
  'recipient@example.com',
  'Sujet',
  '<h1>Contenu HTML</h1>',
  'Contenu texte'
);
```

## Format ml.txt

```
email1@example.com
email2@example.com
email3@example.com
```

## Notes Brevo

- **Host**: `smtp-relay.brevo.com`
- **Port**: 587 (TLS)
- **User**: Ton adresse email Brevo
- **Pass**: Ta clé SMTP Brevo (Settings → SMTP & API)

## Où trouver tes infos Brevo

1. Va sur https://app.brevo.com
2. Settings → SMTP & API
3. Copie ton clé SMTP et tes identifiants
