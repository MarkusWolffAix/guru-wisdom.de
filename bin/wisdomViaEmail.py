#!/opt/homebrew/bin/python3
import subprocess
import os
import tempfile
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.mime.image import MIMEImage
from email.utils import formatdate, make_msgid # NEU: Für Anti-Spam-Header

def send_email(recipient_email, recipient_name):
    """
    Generates and sends an email via msmtp.
    The email content is in German, while the script logic and comments are in English.
    """
    # ==========================================
    # 1. CONFIGURATION
    # ==========================================
    SENDER_EMAIL = "info@guru-wisdom.com"
    SENDER_NAME = "Markus Wolff"
    
    # Path to the original image (automatically resolves $HOME or ~)
    raw_image_path = "$HOME/Downloads/EasterStoryAndSymbols.jpg"
    IMAGE_PATH = os.path.expanduser(os.path.expandvars(raw_image_path))
    
    MAX_IMAGE_WIDTH = "600" # Max width for sips (as string)
    JPEG_QUALITY = "80"     # JPEG quality for sips (as string)
    
    # Unique CID for the image (looks more professional to spam filters)
    IMAGE_CID = "easterimage"
    # ==========================================

    # Use 'related' to embed the image cleanly into the HTML body
    msg = MIMEMultipart('related')
    msg['From'] = f"{SENDER_NAME} <{SENDER_EMAIL}>"
    msg['To'] = recipient_email
    msg['Subject'] = f"Frohe Ostern, liebe {recipient_name}!"
    
    # CRITICAL FIX AGAINST SPAM FILTERS: Add Date and Message-ID
    msg['Date'] = formatdate(localtime=True)
    msg['Message-ID'] = make_msgid(domain="guru-wisdom.com")

    # Create a sub-container for the text (multipart/alternative for plain text + HTML)
    alt_part = MIMEMultipart('alternative')
    msg.attach(alt_part)

    # ------------------------------------------
    # 2. PLAIN TEXT VERSION
    # ------------------------------------------
    text_content = f"""Frohe Ostern, liebe {recipient_name}!

Ich wünsche dir von Herzen wunderschöne, sonnige und entspannte Feiertage! 
Genieße den Frühling, das leckere Essen und hör gerne mal in mein kleines Osterlied rein.

Und falls du dich beim Eiersuchen fragst, warum eigentlich ein Hase die Eier bringt – die spannenden Ursprünge unserer Osterbräuche gibt's hier zum Nachlesen:
https://guru-wisdom.de

Fühl dich fest gedrückt und hab ein tolles Fest!

--------------------------------------------------
🌿 GURU Wisdom GmbH
✨ Worte der Weisheit für deinen Alltag.
      
📍 Sophienstraße 26, 52070 Aachen
🌐 https://guru-wisdom.de
✉️ info@guru-wisdom.com
📞 +49 (0) 241 9199 2862

Geschäftsführer: Markus Wolff
HRB 27892 | 201/5963/5746

"""
    alt_part.attach(MIMEText(text_content, 'plain', 'utf-8'))

    # ------------------------------------------
    # 3. HTML VERSION
    # ------------------------------------------
    html_content = f"""<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<style>
  body {{ font-family: Arial, sans-serif; line-height: 1.6; color: #333333; background-color: #f9f9f9; margin: 0; padding: 20px; }}
  .container {{ max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; }}
  h2 {{ color: #d4a373; text-align: center; margin-bottom: 20px; }}
  .image-box {{ text-align: center; margin: 20px 0; }}
  .image-box img {{ max-width: 100%; height: auto; border-radius: 8px; display: block; margin: 0 auto; }}
  .content p {{ margin-bottom: 15px; }}
  .link-button {{ display: inline-block; color: #ffffff !important; background-color: #d4a373; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 10px; }}
  .footer {{ margin-top: 40px; padding-top: 20px; border-top: 1px solid #eeeeee; font-size: 10px; color: #777777; line-height: 1.3; }}
  .footer a {{ color: #d4a373; text-decoration: none; }}
</style>
</head>
<body>
  <div class="container">
    <h2>🌷✨ Frohe Ostern, liebe/r {recipient_name}! ✨🌷</h2>
    
    <div class="image-box">
      <img src="cid:{IMAGE_CID}" alt="Frohe Ostern">
    </div>

    <div class="content">
      <p>Ich wünsche dir von Herzen wunderschöne, sonnige und entspannte Feiertage! 🐰🐣</p>
      <p>Genieße den Frühling, das leckere Essen und hör gerne mal in mein kleines Osterlied rein.</p>
      <p>👉 Und falls du dich beim Eiersuchen fragst, warum eigentlich ein Hase die Eier bringt – die spannenden Ursprünge unserer Osterbräuche gibt's hier zum Nachlesen:</p>
      <p style="text-align: center;">
        <a href="https://guru-wisdom.de" class="link-button">Artikel auf guru-wisdom.de lesen</a>
      </p>
      <p>Fühl dich fest gedrückt und hab ein tolles Fest! 💛☀️</p>
    </div>

    <div class="footer">
      <strong>GURU Wisdom GmbH</strong><br>
      Sophienstrasse 26 | 52070 Aachen<br><br>
      +49 (0) 241 9199 2862 
      <strong>Geschäftsführer:</strong> Markus Wolff<br>
      <strong>Register:</strong> Amtsgericht Aachen, HRB 27892 | 201/5963/5746<br>
      <strong>Mail:</strong> <a href="mailto:info@guru-wisdom.com">info@guru-wisdom.com</a>
    </div>
  </div>
</body>
</html>
"""
    alt_part.attach(MIMEText(html_content, 'html', 'utf-8'))

    # ------------------------------------------
    # 4. CONVERT IMAGE WITH macOS 'sips'
    # ------------------------------------------
    if os.path.exists(IMAGE_PATH):
        try:
            temp_img_path = os.path.join(tempfile.gettempdir(), "resized_image.jpg")
            
            print("Resizing image using macOS 'sips'...")
            sips_cmd = [
                'sips',
                '-Z', MAX_IMAGE_WIDTH,
                '-s', 'format', 'jpeg',
                '-s', 'formatOptions', JPEG_QUALITY,
                IMAGE_PATH,
                '--out', temp_img_path
            ]
            
            subprocess.run(sips_cmd, check=True, capture_output=True)
            
            with open(temp_img_path, 'rb') as img_file:
                img_data = img_file.read()
                
            image = MIMEImage(img_data, name="GreetingImage.jpg")
            image.add_header('Content-ID', f'<{IMAGE_CID}>')
            image.add_header('Content-Disposition', 'inline', filename="GreetingImage.jpg")
            msg.attach(image)
            
            os.remove(temp_img_path)
            print("📸 Image successfully resized and attached.")
            
        except subprocess.CalledProcessError as e:
            print(f"❌ Error during sips conversion: {e.stderr.decode('utf-8', errors='ignore')}")
        except Exception as e:
            print(f"❌ Unexpected error during image processing: {e}")
    else:
        print(f"⚠️ Warning: Image '{IMAGE_PATH}' not found. Sending email without image.")

    # ------------------------------------------
    # 5. SEND EMAIL VIA MSMTP
    # ------------------------------------------
    try:
        print(f"Sending email to {recipient_email} via msmtp...")
        
        subprocess.run(
            ['msmtp', '-t'],
            input=msg.as_bytes(),
            capture_output=True,
            check=True
        )
        print("✅ Email successfully sent!")
        print("💡 Hinweis: Falls sie nicht ankommt, prüfe bitte deinen Spam-Ordner!")
        
    except subprocess.CalledProcessError as e:
        print(f"❌ Error sending email via msmtp:")
        print(f"Return Code: {e.returncode}")
        print(f"Error Output: {e.stderr.decode('utf-8', errors='ignore')}")
    except FileNotFoundError:
        print("❌ Error: The command 'msmtp' was not found. Please ensure it is installed.")

if __name__ == "__main__":
    send_email("MarkusWolff@gmx.de", "Lara")
