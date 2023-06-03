(function (DrupalServer, content) {
  // DrupalServer object manipulation will update the document content
  // for the current response.
  let bodyTag = content.match(/\<body[^\>]*\>/i)[0];
  DrupalServer.content = content.replace(bodyTag, bodyTag + '<div id="ssr-root"><h1>Say hello to server-side rendered entrypoints.</h1></div>');
})(DrupalServer, DrupalServer.content);
