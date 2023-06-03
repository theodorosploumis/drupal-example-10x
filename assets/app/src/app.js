(function (document) {
  var el = document.querySelector('#ssr-root');
  if (el !== null) {
    el.innerHTML = el.innerHTML + '<h2>... and I got appended on the client-side.</h2>'
  }
})(window.document);
