/**
 * GCC Forms — website config (standard implementation)
 * ----------------------------------------------------
 * 1) Run Apps Script Code.gs → setupUnifiedRegistrationForm
 * 2) Copy Published URL from Logs into REGISTRATION_FORM_URL below
 * 3) Refresh site pages — Register buttons pick this up
 *
 * Flow: ONE continuous Google Form
 *   Personal → Professional → Conference → Pay instructions → Payment confirmation → Submit
 */

window.GCCForms = window.GCCForms || {};

/** Unified Registration Form — details + payment confirmation (single flow) */
window.GCCForms.REGISTRATION_FORM_URL =
  'https://docs.google.com/forms/d/e/1FAIpQLScI1ZniJ5Een3BdO1FpdVIxp6sDUtI7Wn0Czi6w4f1Rsh9IgQ/viewform?usp=sharing&ouid=111957019366978081750';

/** Payment Form — retired (merged into REGISTRATION_FORM_URL) */
window.GCCForms.PAYMENT_FORM_URL = '';

window.GCCForms.ABSTRACT_FORM_URL =
  'https://docs.google.com/forms/d/e/1FAIpQLScfCpbM4O1HZh6mJYtvVYodZY40gV_KYr7rpLKJdVImQDUJQw/viewform?usp=sharing&ouid=111957019366978081750';
  

window.GCCForms.REGISTRATION_PAGE = 'gcc-2027.html#registration-details';

/** Website bank/UPI reference (shown while user fills the form) */
window.GCCForms.PAYMENT_PAGE = 'gcc-2027.html#step-pay';

(function () {
  function apply() {
    var reg = window.GCCForms.REGISTRATION_FORM_URL;
    var pay = window.GCCForms.PAYMENT_FORM_URL;
    var abs = window.GCCForms.ABSTRACT_FORM_URL;
    var page = window.GCCForms.REGISTRATION_PAGE;

    document.querySelectorAll('[data-gcc-link="register-form"]').forEach(function (el) {
      if (reg) el.setAttribute('href', reg);
    });

    document.querySelectorAll('[data-gcc-link="payment-form"]').forEach(function (el) {
      if (pay) {
        el.setAttribute('href', pay);
      } else if (reg) {
        // Fallback: old payment buttons open the unified form
        el.setAttribute('href', reg);
      }
    });

    document.querySelectorAll('[data-gcc-link="abstract-form"]').forEach(function (el) {
      if (abs) el.setAttribute('href', abs);
    });

    document.querySelectorAll('[data-gcc-link="registration-page"]').forEach(function (el) {
      if (page) el.setAttribute('href', page);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', apply);
  } else {
    apply();
  }
})();
