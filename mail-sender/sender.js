const nodemailer = require('nodemailer');
const fs = require('fs');
const path = require('path');

const configPath = path.join(__dirname, 'config.json');
const config = JSON.parse(fs.readFileSync(configPath, 'utf8'));

const transporter = nodemailer.createTransport({
  host: config.smtp.host,
  port: config.smtp.port,
  secure: config.smtp.secure,
  auth: {
    user: config.smtp.auth.user,
    pass: config.smtp.auth.pass
  }
});

async function sendEmail(to, subject, html) {
  try {
    const mailOptions = {
      from: `${config.sender.fromName} <${config.sender.from}>`,
      to,
      subject,
      html: html || '<p>No HTML content</p>'
    };

    const info = await transporter.sendMail(mailOptions);
    console.log('✅ Email sent to:', to, '|', info.messageId);
    return info;
  } catch (error) {
    console.error('❌ Error sending email to', to, ':', error.message);
    throw error;
  }
}

async function sendEmailWithDelay(to) {
  return new Promise((resolve, reject) => {
    setTimeout(async () => {
      try {
        const result = await sendEmail(to, config.email.object, config.htmlContent, config.email.textFallback);
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

    console.log(`\n📧 Starting bulk email sending...`);
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

module.exports = { sendEmail, sendBulkEmails, transporter };
