Profile pictures uploaded from views/profile.php are saved here.

This file only exists so the folder itself is created. profile_control.php
calls move_uploaded_file() straight into this folder and does not create
it, so the folder has to be here before the first upload.

You can delete this readme once there is a picture in here.
