(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
  typeof define === 'function' && define.amd ? define(factory) :
  (global = typeof globalThis !== 'undefined' ? globalThis : global || self, global.RModal = factory());
}(this, (function () {
  var splitClasses = function (classes) { return (("" + (classes || ''))
      .split(' ')
      .filter(function (cls) { return !!cls; })); };
  var addClass = function (el, classes) {
      splitClasses(classes).forEach(function (cls) {
          el.classList.add(cls);
      });
  };
  var removeClass = function (el, classes) {
      splitClasses(classes).forEach(function (cls) {
          el.classList.remove(cls);
      });
  };
  var RModal = /** @class */ (function () {
      function RModal(el, opts) {
          var _this = this;
          if (opts === void 0) { opts = {}; }
          this.version = '1.1.0';
          this.focusOutElement = null;
          this.opened = false;
          this.opts = {
              bodyClass: 'modal-open',
              dialogClass: 'modal-dialog',
              dialogOpenClass: 'bounceInDown',
              dialogCloseClass: 'bounceOutUp',
              focus: true,
              focusElements: [
                  'a[href]', 'area[href]', 'input:not([disabled]):not([type=hidden])',
                  'button:not([disabled])', 'select:not([disabled])',
                  'textarea:not([disabled])', 'iframe', 'object', 'embed',
                  '*[tabindex]', '*[contenteditable]'
              ],
              escapeClose: true,
              content: undefined,
              closeTimeout: 500
          };
          Object.keys(opts).forEach(function (key) {
              var optionsKey = key;
              /* istanbul ignore else */
              if (opts[optionsKey] !== undefined) {
                  _this.opts[optionsKey] = opts[optionsKey];
              }
          });
          this.overlay = el;
          this.dialog = el.querySelector("." + this.opts.dialogClass);
          if (this.opts.content) {
              this.content(this.opts.content);
          }
      }
      RModal.prototype.open = function (content) {
          var _this = this;
          this.content(content);
          if (typeof this.opts.beforeOpen !== 'function') {
              return this._doOpen();
          }
          this.opts.beforeOpen(function () {
              _this._doOpen();
          });
      };
      RModal.prototype._doOpen = function () {
          addClass(document.body, this.opts.bodyClass);
          removeClass(this.dialog, this.opts.dialogCloseClass);
          addClass(this.dialog, this.opts.dialogOpenClass);
          this.overlay.style.display = 'block';
          if (this.opts.focus) {
              this.focusOutElement = document.activeElement;
              this.focus();
          }
          if (typeof this.opts.afterOpen === 'function') {
              this.opts.afterOpen();
          }
          this.opened = true;
      };
      RModal.prototype.close = function () {
          var _this = this;
          if (typeof this.opts.beforeClose !== 'function') {
              return this._doClose();
          }
          this.opts.beforeClose(function () {
              _this._doClose();
          });
      };
      RModal.prototype._doClose = function () {
          var _this = this;
          removeClass(this.dialog, this.opts.dialogOpenClass);
          addClass(this.dialog, this.opts.dialogCloseClass);
          removeClass(document.body, this.opts.bodyClass);
          if (this.opts.focus) {
              this.focus(this.focusOutElement);
          }
          setTimeout(function () {
              _this.overlay.style.display = 'none';
              if (typeof _this.opts.afterClose === 'function') {
                  _this.opts.afterClose();
              }
              _this.opened = false;
          }, this.opts.closeTimeout);
      };
      RModal.prototype.content = function (html) {
          if (html === undefined) {
              return this.dialog.innerHTML;
          }
          this.dialog.innerHTML = html;
      };
      RModal.prototype.elements = function (selector, fallback) {
          fallback = fallback || window.navigator.appVersion.indexOf('MSIE 9.0') > -1;
          selector = Array.isArray(selector) ? selector.join(',') : selector;
          return [].filter.call(this.dialog.querySelectorAll(selector), function (element) {
              if (fallback) {
                  var style = window.getComputedStyle(element);
                  return style.display !== 'none' && style.visibility !== 'hidden';
              }
              return element.offsetParent !== null;
          });
      };
      RModal.prototype.focus = function (el) {
          el = el || this.elements(this.opts.focusElements || '')[0] || this.dialog.firstChild;
          if (el && typeof el.focus === 'function') {
              el.focus();
          }
      };
      RModal.prototype.keydown = function (ev) {
          if (this.opts.escapeClose && ev.which == 27) {
              this.close();
          }
          var stopEvent = function () {
              ev.preventDefault();
              ev.stopPropagation();
          };
          if (this.opened && ev.which == 9 && this.dialog.contains(ev.target)) {
              var elements = this.elements(this.opts.focusElements || ''), first = elements[0], last = elements[elements.length - 1];
              if (first == last) {
                  stopEvent();
              }
              else if (ev.target == first && ev.shiftKey) {
                  stopEvent();
                  last.focus();
              }
              else if (ev.target == last && !ev.shiftKey) {
                  stopEvent();
                  first.focus();
              }
          }
      };
      RModal.version = '1.1.0';
      return RModal;
  }());

  return RModal;

})));
//# sourceMappingURL=rmodal.js.map
