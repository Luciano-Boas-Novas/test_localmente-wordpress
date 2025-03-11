"use strict";

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
var UltimateBlocksCounter = /*#__PURE__*/function () {
  function UltimateBlocksCounter(wrapper) {
    _classCallCheck(this, UltimateBlocksCounter);
    this.container = wrapper;
    this.counterNumber = this.container.querySelector(".ub_counter-number");
    this.startCount = parseInt(this.container.dataset.start_num, 10);
    this.stopCounter = parseInt(this.container.dataset.end_num, 10);
    this.animationDuration = parseInt(this.container.dataset.animation_duration, 10);
    this.frameDuration = 1000 / 60;
    this.totalFrames = Math.round(this.animationDuration * 1000 / this.frameDuration);
    this.easeOutQuad = function (t) {
      return t * (2 - t);
    };
  }
  return _createClass(UltimateBlocksCounter, [{
    key: "initialize",
    value: function initialize() {
      this.updateCounter();
    }
  }, {
    key: "updateCounter",
    value: function updateCounter() {
      var _this = this;
      var frame = 0;
      var countTo = this.stopCounter - this.startCount;
      var interval = setInterval(function () {
        frame++;
        var progress = _this.easeOutQuad(frame / _this.totalFrames);
        var currentCount = Math.round(countTo * progress) + _this.startCount;
        if (parseInt(_this.counterNumber.innerHTML, 10) !== currentCount) {
          _this.counterNumber.innerHTML = currentCount;
        }
        if (frame === _this.totalFrames) {
          clearInterval(interval);
        }
      }, this.frameDuration);
    }
  }]);
}();
window.addEventListener("DOMContentLoaded", function () {
  var container = document.querySelectorAll(".ub_counter");
  var observerOptions = {
    root: null,
    rootMargin: "0px",
    threshold: 0.1
  };
  var observer = new IntersectionObserver(function (entries, observer) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        var wrapper = entry.target;
        new UltimateBlocksCounter(wrapper).initialize();
        observer.unobserve(wrapper); // Unobserve after initializing to avoid re-triggering
      }
    });
  }, observerOptions);
  container.forEach(function (wrapper) {
    return observer.observe(wrapper);
  });
});