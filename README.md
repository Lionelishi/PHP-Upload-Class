#PHP Upload Class

##Security features

<ul>
<li>Limits maximum size of individual files - use setMaxFileSize() method on class instance.</li>
<li>Checks MIME type or renames risky types</li>
</ul>

##Flexibility

<ul>
<li>Handles single or multiple files uploads</li>
<li>Easy to configure</li>
</ul>

##Default Settings

<ul>
<li>Maximum file size set to 50KB</li>
<li>Restricted to image and pdf files</li>
<li>Appends '.upload' to risky filename extensions</li>
<li>Duplicate files are automatically renamed</li>
</ul>

All of these can be changed easy by calling public methods.

####To avoid compatibility issues class uses namespace (PHP 5.3 or later required).

##Usage

<p>Before including class in your php page, import namespace with </p>
<pre>
<code>use milanpetrovic\UploadFile;</code>
</pre>
<p>Then create UploadFile object. Pass it path to the directory in which files will be uploaded as constructor argument</p>
<pre>
<code>$upload = new UploadFile($destination);</code>
</pre>

###Class has 4 public methods:

<strong>setMaxFileSize() - optional</strong> - Changes the file size limit for individual files. Takes a single argument which must be expressed as number of bytes.
<pre>
<code>$upload->setMaxFileSize($bytes);</code>
</pre>

<strong>allowAllTypes() - optional</strong> - Remove restrictions from file types that can be uploaded. Takes a single, optional argument for custom suffix. If argument is not passed, method will append default '.upload' suffix to risky files.
<pre>
<code>$upload->allowAllTypes($suffix = null);</code>
</pre>
To leave file names unchanged, pass empty string as argument.<br>
Using this method is optional. If it's not used, class will restrict file types to those listed in $allowedTypes property.

<strong>upload() - required</strong> - Method that performs upload. Takes a single, optional argument. Must be called after setMaxFileSize() and allowAllTypes() methods.
<pre>
<code>$upload->upload($renameDuplicates = true);</code>
</pre>
Using this method without an argument will automatically rename duplicated files. Passing false means that duplicated files will be overwritten. 

<strong>getMessages() - recommended</strong> - Returns array of messages reporting outcome (both success and failure). Must be called after upload() method. Takes no arguments.
<pre>
<code>
$messages = array(); 
$messages = $upload->getMessages();
</code>
</pre>

### Static methods
There are two static methods: <strong>convertFromBytes()</strong> and <strong>convertToBytes()</strong> that convert bytes into human readable size, and server limit
into bytes.
<pre>
<code>
UploadFile::convertFromBytes($bytes);
UploadFile::convertToBytes($value);
</code>
</pre>

<hr>
<strong>Note: </strong> On demo, add margin-top of 100px at least to see form fields.