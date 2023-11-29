const express = require('express');
const app = express();
const { exec } = require('child_process');
const fs = require('fs');
const cssDirPath = '/var/paravel/apps/cli';

app.use(express.text());

app.post('/generate-mermaid', (req, res) => {
    let mermaidSyntax = req.body;

mermaidSyntax = mermaidSyntax.replace(/^```mermaid/, '')   // Rem
.replace(/\\u00e5/g, 'å')   // Replace \u00e5 with å
.replace(/\\u00e4/g, 'ä')   // Replace \u00e4 with ä
.replace(/\\u00f6/g, 'ö')   // Replace \u00f6 with ö
.replace(/\\u00c5/g, 'Å')   // Replace \u00c5 with Å (uppercase Å)
.replace(/\\u00c4/g, 'Ä')   // Replace \u00c4 with Ä (uppercase Ä)
.replace(/\\u00d6/g, 'Ö')   // Replace \u00d6 with Ö (uppercase Ö)
			      .replace(/mermaid/g, '')
			      .replace(/```\//g, '') // Remove occurrences of three backticks followed by a forward slash
                              .replace(/```/g, '') // Remove occurrences of three backticks
			      .replace(/^;/, '')             // Remove a leading semicolon if it exists
			      .replace(/^"|"$/g, '')
			      .replace(/\\\//g, '/')  // Replace '\/' with '/'
			      .replace(/\\n/g, '\n')
//                              .replace(/;;/, '')             // Remove ;; if it exists
//			      .replace(/end /, 'end;')             // Remove ;; if it exists
// 			      .replace(/end;    ;/, 'end;     ');             // Remove ;; if it exists
			      .replace(/\u0008/g, ''); // Remove backspace ("\b") characters
   const timestamp = Date.now();
    const inputFilePath = `/var/www/published_content/cli/mermaid_${timestamp}.mmd`;
    const outputFilePath = `/var/www/published_content/cli/image_${timestamp}.svg`;
    const publicOutputFilePath = `/var/www/published_content/cli/image_${timestamp}.svg`;

    fs.writeFileSync(inputFilePath, mermaidSyntax);


    exec(`docker run --rm -v /var/www/published_content/cli:/data -v ${cssDirPath}:/css minlag/mermaid-cli:10.6.0 mmdc -i /data/mermaid_${timestamp}.mmd -o /data/image_${timestamp}.svg -C /css/transparent-background.css`, (error, stdout, stderr) => {
        if (error) {
            console.error(`exec error: ${error}`);
            res.status(500).json({
                error: 'Error generating image',
                errorMessage: error.message,
                errorDetails: stderr
            });
            return;
        }

          // No need to copy the file, as it's already in the public directory
            // Send the SVG URL as a response
            res.send(`https://laravel.panacean.it/cli_images/image_${timestamp}.svg`);
        });
   
        
        

    
});

const PORT = 3000;
app.listen(PORT, () => {
    console.log(`Server is running on port ${PORT}`);
});
