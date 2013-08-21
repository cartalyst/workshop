workshop
========

Integration with Cartalyst's Extensions for Laravel's Workbench

####Quickstart Method 1

Step 1:

Create your extension using the Workshop

Step 2:

Open your main composer.json and add the following lines into the "classmap" array of your "autoload"

```
"workbench/<vendor>/<extension>/controllers",
"workbench/<vendor>/<extension>/models",
"workbench/<vendor>/<extension>/database/migrations",
"workbench/<vendor>/<extension>/database/seeds",
"workbench/<vendor>/<extension>/tests",
"workbench/<vendor>/<extension>/widgets"
```

In the end, the classmap array should look like this
```
"autoload": {
	"classmap": [
		"app/commands",
		"app/controllers",
		"app/models",
		"app/overrides",
		"app/widgets",
		"app/database/migrations",
		"app/database/seeds",
		"app/tests/TestCase.php",
		
		"workbench/<vendor>/<extension>/controllers",
		"workbench/<vendor>/<extension>/models",
		"workbench/<vendor>/<extension>/database/migrations",
		"workbench/<vendor>/<extension>/database/seeds",
		"workbench/<vendor>/<extension>/tests",
		"workbench/<vendor>/<extension>/widgets"
	]
},
```

Step 3:

Do a composer dump-autoload

Step 4:

All done!
    
####Quickstart Method 2


Step 1:

Create your extension using the Workshop

Step 2:

On the extension folder you create a new folder structure like so
```  
src
	<vendor>
		<extension>
			Controllers
			Models
			Widgets
                    
```                    

The folders should start with a capitalised letter, so in the end it should look like this

```    
src
	Brunog
		Myextension
			Controllers
			Models
			Widgets
```

Step 3:

Open your extension.php file and modify the autoload from "composer" to "platform2
    
Step 4:

Done!
