(function (global, factory) {
  if (typeof define === "function" && define.amd) {
    define(["exports"], factory);
  } else if (typeof exports !== "undefined") {
    factory(exports);
  } else {
    var mod = {
      exports: {}
    };
    factory(mod.exports);
    global.smallCartTpl = mod.exports;
  }
})(typeof globalThis !== "undefined" ? globalThis : typeof self !== "undefined" ? self : this, function (_exports) {
  "use strict";

  Object.defineProperty(_exports, "__esModule", {
    value: true
  });
  _exports.default = void 0;
  function _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = "function" == typeof Symbol ? Symbol : {}, n = r.iterator || "@@iterator", o = r.toStringTag || "@@toStringTag"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, "_invoke", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError("Generator is already running"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = "next"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i.return) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, "toString", function () { return "[object Generator]"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }
  function _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, "", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2)); }, _regeneratorDefine2(e, r, n, t); }
  function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
  function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
  var _default = _exports.default = {
    template: "<div class=\"header__panel-btn\" @mouseenter=\"open\" @mouseleave=\"close\" @click=\"goToCart\"> <svg class=\"header__panel-btn-icon header__panel-btn-icon--cart\" width=\"24\" height=\"24\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\"> <path d=\"M2.25 3H3.636C4.146 3 4.591 3.343 4.723 3.835L5.106 5.272M5.106 5.272C10.6766 5.11589 16.2419 5.73515 21.642 7.112C20.818 9.566 19.839 11.95 18.718 14.25H7.5M5.106 5.272L7.5 14.25M7.5 14.25C6.70435 14.25 5.94129 14.5661 5.37868 15.1287C4.81607 15.6913 4.5 16.4544 4.5 17.25H20.25M6 20.25C6 20.4489 5.92098 20.6397 5.78033 20.7803C5.63968 20.921 5.44891 21 5.25 21C5.05109 21 4.86032 20.921 4.71967 20.7803C4.57902 20.6397 4.5 20.4489 4.5 20.25C4.5 20.0511 4.57902 19.8603 4.71967 19.7197C4.86032 19.579 5.05109 19.5 5.25 19.5C5.44891 19.5 5.63968 19.579 5.78033 19.7197C5.92098 19.8603 6 20.0511 6 20.25ZM18.75 20.25C18.75 20.4489 18.671 20.6397 18.5303 20.7803C18.3897 20.921 18.1989 21 18 21C17.8011 21 17.6103 20.921 17.4697 20.7803C17.329 20.6397 17.25 20.4489 17.25 20.25C17.25 20.0511 17.329 19.8603 17.4697 19.7197C17.6103 19.579 17.8011 19.5 18 19.5C18.1989 19.5 18.3897 19.579 18.5303 19.7197C18.671 19.8603 18.75 20.0511 18.75 20.25Z\" stroke=\"currentColor\" stroke-width=\"1.5\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/> </svg> <span class=\"header__panel-btn-text\">Корзина</span> <span v-if=\"storeItems.length > 0\" class=\"cart-counter\">{{ storeTotalQty }}</span> <template v-if=\"storeItems && storeItems.length > 0\"> <div v-if=\"show\" class=\"cart-popup\" @click.stop> <div class=\"cart-content\"> <div v-for=\"item in storeItems\" :key=\"item.basketId || item.productId\" class=\"cart-item\"> <div class=\"cart-item-info\"> <div class=\"cart-item-name\"> <a v-if=\"item.url\" :href=\"item.url\" class=\"cart-item-name cart-item-name__link\">{{ item.name }}</a> <span v-else>{{ item.name }}</span> </div> </div> <div class=\"cart-item-actions\"> <div class=\"cart-item-sum\" v-html=\"item.pricePrint\"></div> <button class=\"cart-item-remove\" @click=\"removeItem(item.basketId)\" title=\"Удалить товар\" > <svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"> <path d=\"m18 6-12 12M6 6l12 12\"/> </svg> </button> </div> </div> <div class=\"cart-total\"> <span>Итого:</span> <span v-html=\"storeTotalSumPrint\"></span> </div> <a href=\"/cart/\" class=\"cart-btn\">Перейти в корзину</a> </div> </div> </template> </div>",
    name: 'DevobSmallCart',
    data: function data() {
      return {
        show: false,
        removing: false
      };
    },
    setup: function setup() {
      if (!window.useCartStore || !window.devobPinia) {
        return {
          storeItems: [],
          storeTotalQty: 0,
          storeTotalSum: 0,
          storeTotalSumPrint: 0,
          cart: null
        };
      }
      var cart = window.useCartStore(window.devobPinia);

      // Инициализируем store данными из PHP
      if (cart.items.length === 0) {
        var _window$BitrixVueData, _window$BitrixVueData2, _window$BitrixVueData3;
        var phpItems = typeof window !== 'undefined' && window.BitrixVueData ? (_window$BitrixVueData = window.BitrixVueData['devob-small-cart']) === null || _window$BitrixVueData === void 0 ? void 0 : _window$BitrixVueData.items : [];
        var phpTotalQty = typeof window !== 'undefined' && window.BitrixVueData ? (_window$BitrixVueData2 = window.BitrixVueData['devob-small-cart']) === null || _window$BitrixVueData2 === void 0 ? void 0 : _window$BitrixVueData2.totalqty : 0;
        var phpTotalSum = typeof window !== 'undefined' && window.BitrixVueData ? (_window$BitrixVueData3 = window.BitrixVueData['devob-small-cart']) === null || _window$BitrixVueData3 === void 0 ? void 0 : _window$BitrixVueData3.totalsum : 0;
        if (phpItems && phpItems.length > 0) {
          cart.load({
            items: phpItems,
            totalQty: phpTotalQty || 0,
            totalSum: phpTotalSum || 0
          });
        }
      }

      // Используем storeToRefs для реактивности
      if (window.Pinia && window.Pinia.storeToRefs) {
        var _window$Pinia$storeTo = window.Pinia.storeToRefs(cart),
          storeItems = _window$Pinia$storeTo.items,
          storeTotalQty = _window$Pinia$storeTo.totalQty,
          storeTotalSum = _window$Pinia$storeTo.totalSum,
          storeTotalSumPrint = _window$Pinia$storeTo.totalSumPrint;
        return {
          storeItems: storeItems,
          storeTotalQty: storeTotalQty,
          storeTotalSum: storeTotalSum,
          storeTotalSumPrint: storeTotalSumPrint,
          cart: cart
        };
      } else {
        return {
          storeItems: Vue.computed(function () {
            return cart.items;
          }),
          storeTotalQty: Vue.computed(function () {
            return cart.totalQty;
          }),
          storeTotalSum: Vue.computed(function () {
            return cart.totalSum;
          }),
          storeTotalSumPrint: Vue.computed(function () {
            return cart.totalSumPrint;
          }),
          cart: cart
        };
      }
    },
    methods: {
      open: function open() {
        this.show = true;
      },
      close: function close() {
        this.show = false;
      },
      goToCart: function goToCart() {
        window.location.href = '/cart/';
      },
      removeItem: function removeItem(basketId) {
        var _this = this;
        return _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {
          var response, _response$data, _t;
          return _regenerator().w(function (_context) {
            while (1) switch (_context.p = _context.n) {
              case 0:
                if (!_this.removing) {
                  _context.n = 1;
                  break;
                }
                return _context.a(2);
              case 1:
                _this.removing = true;
                _context.p = 2;
                _context.n = 3;
                return BX.ajax.runComponentAction('devob:cart', 'remove', {
                  mode: 'class',
                  data: {
                    basketItemId: basketId
                  }
                });
              case 3:
                response = _context.v;
                if (response.data && response.data.success) {
                  // Обновляем store с новыми данными с сервера
                  _this.cart.load({
                    items: response.data.items || [],
                    totalQty: response.data.totalQty || 0,
                    totalSum: response.data.totalSum || 0
                  });
                } else {
                  console.error('Ошибка удаления:', (_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.error);
                }
                _context.n = 5;
                break;
              case 4:
                _context.p = 4;
                _t = _context.v;
                console.error('Ошибка сети при удалении:', _t);
              case 5:
                _context.p = 5;
                _this.removing = false;
                return _context.f(5);
              case 6:
                return _context.a(2);
            }
          }, _callee, null, [[2, 4, 5, 6]]);
        }))();
      }
    }
  };
});