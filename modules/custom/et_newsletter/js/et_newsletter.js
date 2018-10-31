(function ($, Drupal) {
  /**
   * Implements Drupal.Ajax.prototype.beforeSubmit.
   */
  Drupal.Ajax.prototype.beforeSubmit = function (form_values, element, options) {
    this.ajaxing = false;

    // Get the form id.
    var form_id = form_values.find(function(value) {
      return value.name == 'form_id';
    });

    // Verify that we are on the newsletter subscribe form.
    if (form_id !== null && typeof form_id === 'object' && form_id.value.lastIndexOf('et_newsletter_subscribe_', 0) === 0) {
      var $newsletterForm = element;
      var isValid = true;
      var $errorMessages = $newsletterForm.find('.newsletter-error-message');
      var $errorMessageEmail = $newsletterForm.find('.newsletter-error-message--email');
      var $errorMessageTerms = $newsletterForm.find('.newsletter-error-message--terms');
      var $successMessages = $newsletterForm.find('.newsletter-success-message');
      var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;

      // Hide all error and success messages.
      $successMessages.hide();
      $errorMessages.hide();

      // Validate terms.
      var terms = $newsletterForm.find('input[name="terms"]');
      if (terms.length && !terms.is(':checked')) {
        $errorMessageTerms.show();
        isValid = false;
      }

      // Validate email.
      var email = $newsletterForm.find('input[type="email"]').val();
      if (!email || !regex.test(email)) {
        $errorMessageEmail.show();
        isValid = false;
      }

      return isValid;
    }

    this.ajaxing = true;
    return true;
  };
})(jQuery, Drupal);
