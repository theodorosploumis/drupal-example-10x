(function (Drupal, once) {
  Drupal.behaviors.cta = {
    attach: function attach(context) {
      const [cta] = once('sdc--my-cta', '.sdc--my-cta', context);
      console.log(cta);
    },
  };
})(Drupal, once);
