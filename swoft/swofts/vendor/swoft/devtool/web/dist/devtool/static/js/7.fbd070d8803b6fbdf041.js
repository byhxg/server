webpackJsonp([7],{h3H1:function(t,e){},tBTA:function(t,e,a){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var n=a("4YfN"),r=a.n(n),o=a("W4j8"),s=a.n(o),c=a("KatZ"),i=a("ZXMi"),l={name:"app-config",components:r()({VAlert:s.a},c),data:function(){return{dataMap:{}}},created:function(){this.fetchList()},mounted:function(){},computed:{},methods:{fetchList:function(){var t=this;Object(i.d)().then(function(e){var a=e.data;t.dataMap=a,console.log(a)})}}},d={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("v-layout",{attrs:{row:"",wrap:""}},[a("v-flex",{attrs:{xs12:""}},[a("v-subheader",[a("h1",[t._v(t._s(this.$route.name))])])],1),t._v(" "),a("v-flex",{attrs:{"d-flex":"",xs12:"",md4:""}},[a("v-card",[a("v-card-title",{staticClass:"title grey lighten-3"},[t._v("Some Tips")]),t._v(" "),a("v-card-text",[a("p",[t._v("get config: "),a("code",[t._v("\\bean('config')->get(key, default = null)")])])])],1)],1),t._v(" "),a("v-flex",{attrs:{"d-flex":"",xs12:"",md8:""}},[a("v-card",{staticClass:"pa-3",attrs:{color:"amber lighten-5"}},[a("tree-view",{attrs:{data:t.dataMap,options:{maxDepth:2,rootObjectKey:"config"}}})],1)],1)],1)},staticRenderFns:[]};var f=a("Z0/y")(l,d,!1,function(t){a("h3H1")},null,null);e.default=f.exports}});
//# sourceMappingURL=7.fbd070d8803b6fbdf041.js.map