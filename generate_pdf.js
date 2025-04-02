const fs = require('fs');
const pdf = require('html-pdf');

// Read the HTML file
const html = fs.readFileSync('./sistema_resumen.html', 'utf8');

// PDF options
const options = {
  format: 'A4',
  border: {
    top: '20mm',
    right: '20mm',
    bottom: '20mm',
    left: '20mm'
  },
  footer: {
    height: '10mm',
    contents: {
      default: '<span style="color: #444; font-size: 10px; text-align: center;">Resumen del Sistema MiClinica - Página {{page}} de {{pages}}</span>',
    }
  }
};

// Generate PDF
pdf.create(html, options).toFile('./sistema_resumen.pdf', function(err, res) {
  if (err) {
    console.error('Error generating PDF:', err);
    return;
  }
  console.log('PDF successfully created at:', res.filename);
});