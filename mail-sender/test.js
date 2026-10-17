const { sendEmail, sendBulkEmails } = require('./sender');

async function main() {
  try {
    // Option 1: Envoyer un email unique
    // await sendEmail(
    //   'recipient@example.com',
    //   'Hello from Node.js Mail Sender',
    //   '<h1>Welcome!</h1><p>This is a test email</p>'
    // );

    // Option 2: Envoyer à toute la liste (ml.txt) avec le template (letter.html)
    await sendBulkEmails();
  } catch (error) {
    console.error('Error:', error.message);
    process.exit(1);
  }
}

main();
