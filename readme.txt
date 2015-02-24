===  woocommerce-total-web-solutions-gateway ===
Contributors:morg0n
Donate link: N/A
Tags: TWS, woocommerce, woothemes, payment
Requires at least: 3.3
Tested up to: 3.5.1
Stable tag: 1.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

TWS payment gateway for Woocommerce.  

== Description ==

The Total Web Solutions plugin for the Woocommerce shopping cart.

Once this has been installed, the module can be configured through the
Woocommerce payment section.

Enable the payment gateway and configure the account as described in the
'Installation' section.

The module should first be set to test mode and the TWS payment script used.

Note - an account with Total Web Solutions is required before using this
module. Please contact sales@totalwebsolutions.com for more information.

== Installation ==

Upload this to your server when the wordpress and WooCommerce are installed
via FTP or other file transfer method to the wordpress/wp-content/plugins
directory

The zip file needs to be unzipped as follows :

cd wordpress/wp-content/plugins
unzip wc-gateway-tws.zip

Login to Word press as Administrator 

Click on the Plugins menu on the left hand side.

You should see the Total Web Solutions Gateway listed. Click the Activate Link
for this gateway.
Next we need to configure the Plugin in WooCommerce, so from the left hand
menu select WooCommerce and the Settings.

Then Click Payment Gateways from the Top Menu.

Click on the Total Web Solutions Link just below the top tabbed menu.

Click the Enable/Disable Box to enable this gateway.

Enter your Total Web Solutions Customer ID into the API Login ID box. 

The Secret Password is a password to be entered that matches the secret key
entered in the Total Web Solutions Ecom administration tool. This ensures that
orders that are marked as paid have not been spoofed. 
The transaction mode can be Live or Test.

To enable Debug click the Enable logging box.

Click Save Changes and module configured and ready to be tested.

== Frequently asked questions ==

= What versions of WooCommerce is this compatable with? =

At the moment, this plugin has been tested and is known to work up to version
2.3.5. If you are using a later version, please contact us regarding this.

= Where can I get more information? =

Please contact support@totalwebsolutions.com if you have any questions about
the installation of the module. If you would like to setup an account to use
with this module, please Email sales@totalwebsolutions.com.

== Changelog ==

= Version 1.1.0 - 20150224 =
Removed depriciated elements for WooCommerce 2.3

= Version 1.0 - 20130520 =
Initial release
