Opauth-Foursquare
=============
[Opauth][1] strategy for Foursquare authentication.

Implemented based on https://developer.foursquare.com/overview/auth

Getting started
----------------
1. Install Opauth-Foursquare:
   ```bash
   cd path_to_opauth/Strategy
   git clone git://github.com/pocket7878/opauth-foursquare.git Foursquare
   ```

2. Create Foursquare application at https://foursquare.com/oauth/register

3. Configure Opauth-Foursquare strategy with at least `Client ID` and `Client Secret`.

4. Direct user to `http://path_to_opauth/foursquare` to authenticate

Strategy configuration
----------------------

Required parameters:

```php
<?php
'Foursquare' => array(
	'client_id' => 'YOUR CLIENT ID',
	'client_secret' => 'YOUR CLIENT SECRET'
)
```

Refer to [Connecting - foursquare](https://developer.foursquare.com/overview/auth.html).

License
---------
Opauth-Facebook is MIT Licensed  
Copyright © 2012 Masato Sogame (Pocket7878) (http://poketo7878.dip.jp)

[1]: https://github.com/uzyn/opauth
