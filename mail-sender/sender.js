const axios = require('axios');
const fs = require('fs');
const path = require('path');

const configPath = path.join(__dirname, 'config.json');
const config = JSON.parse(fs.readFileSync(configPath, 'utf8'));

const brevoApi = axios.create({
  baseURL: 'https://api.brevo.com/v3',
  headers: {
    'api-key': config.brevo.apiKey,
    'Content-Type': 'application/json'
  }
});

async function sendEmail(to, subject, html) {
  try {
    const payload = {
      to: [{ email: to }],
      sender: {
        email: config.sender.from,
        name: config.sender.fromName
      },
      subject,
      htmlContent: html || '<p>No HTML content</p>'
    };

    const response = await brevoApi.post('/smtp/email', payload);
    console.log('✅ Email sent to:', to, '| Message ID:', response.data.messageId);
    return response.data;
  } catch (error) {
    const errorMsg = error.response?.data?.message || error.message;
    console.error('❌ Error sending email to', to, ':', errorMsg);
    throw error;
  }
}

async function sendEmailWithDelay(to) {
  return new Promise((resolve, reject) => {
    setTimeout(async () => {
      try {
        const result = await sendEmail(to, config.email.object, config.htmlContent);
        resolve(result);
      } catch (error) {
        reject(error);
      }
    }, config.delay);
  });
}

async function sendBulkEmails() {
  try {
    // Lire la liste d'emails
    const mailListPath = path.join(__dirname, config.mailList);
    const mailListContent = fs.readFileSync(mailListPath, 'utf8');
    const emailList = mailListContent.split('\n').filter(email => email.trim());

    // Lire le template HTML
    const templatePath = path.join(__dirname, config.email.templateFile);
    const htmlContent = fs.readFileSync(templatePath, 'utf8');
    config.htmlContent = htmlContent;

    console.log(`\n📧 Starting bulk email sending via Brevo API...`);
    console.log(`📋 Found ${emailList.length} recipients`);
    console.log(`⏱️  Delay between emails: ${config.delay}ms\n`);

    let successCount = 0;
    let failureCount = 0;

    for (let i = 0; i < emailList.length; i++) {
      const email = emailList[i].trim();
      try {
        if (i === 0) {
          await sendEmail(email, config.email.object, htmlContent);
        } else {
          await sendEmailWithDelay(email);
        }
        successCount++;
      } catch (error) {
        failureCount++;
      }
    }

    console.log(`\n✅ Bulk email sending completed!`);
    console.log(`📈 Success: ${successCount} | ❌ Failed: ${failureCount}`);
    return { successCount, failureCount, total: emailList.length };
  } catch (error) {
    console.error('❌ Bulk sending error:', error.message);
    throw error;
  }
}

module.exports = { sendEmail, sendBulkEmails };
