require('process');
var DrupalServer = JSON.parse(process.argv[2]);
!function(e,r){var t=r.match(/\<body[^\>]*\>/i)[0];e.content=r.replace(t,t+'<div id="ssr-root"><h1>Say hello to server-side rendered entrypoints.</h1></div>')}(DrupalServer,DrupalServer.content);
process.stdout.write(JSON.stringify(DrupalServer));
