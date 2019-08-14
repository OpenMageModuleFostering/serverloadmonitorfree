/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
Thank you for choosing AITOC!

Please read Installation-Uninstallation Guide.pdf before starting the installation of the module. We also recommend to read the User Manual. 

To open .pdf format, please download acrobat reader at http://www.adobe.com/products/acrobat/readstep2.html (free of charge).


IMPORTANT!

1. Free version of the extension has no automated installer. The extension downloaded from Magento Connect is already enabled. The extension downloaded from www.aitoc.com needs to be enabled manually in /app/etc/modules/Aitoc_Aitloadmonpaid.xml 
<modules>
  <Aitoc_Aitloadmonpaid>
     <active>false</active> // set true 
     <codePool>local</codePool>
     <self_name>Server Load Monitor Paid</self_name>
  </Aitoc_Aitloadmonpaid>
</modules>

2. Paid version package includes an automated installer.

3. For both free and paid versions admin should add the following code to the beginning of your index.php to start monitoring:

include_once(dirname($_SERVER['SCRIPT_FILENAME']).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'Aitoc'.DIRECTORY_SEPARATOR.'Aitloadmon'.DIRECTORY_SEPARATOR.'Collect.php');new Aitoc_Aitloadmon_Collect();




Best Regards,
AITOC team
www.aitoc.com
