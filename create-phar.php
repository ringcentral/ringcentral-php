<?php

@unlink('./dist/ringcentral.phar');

$phar = new Phar(
    './dist/ringcentral.phar',
    FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
    'ringcentral.phar'
);

function listDir($path, $phar)
{

    $relPath = str_replace('/lib', '', $path);

    $it = new DirectoryIterator(__DIR__ . $path);
    foreach ($it as $fileinfo) {
        $filename = $fileinfo->getFilename();
        if ($fileinfo->isDot() || stristr($filename, 'Test.php')) {
            continue;
        } elseif ($fileinfo->isDir()) {
            listDir($path . '/' . $filename, $phar);
        } else {
            $key = ($relPath ? $relPath . '/' : '') . $filename;
            $phar[$key] = file_get_contents(__DIR__ . $path . '/' . $fileinfo->getFilename());
            //print $key . ' -> ' . $path . '/' . $filename . PHP_EOL;
        }
    }

}

listDir('/lib', $phar);

$phar->setStub($phar->createDefaultStub("autoload.php"));

//////////

require('./dist/ringcentral.phar');

$sdk = new RingCentral\SDK('foo', 'bar', 'http://server');

$url = $sdk->getPlatform()->apiUrl('/foo', array('addServer' => true));

if ($url != 'http://server/restapi/v1.0/foo') {
    print 'Failed to verify PHAR' . PHP_EOL;
    exit(1);
}