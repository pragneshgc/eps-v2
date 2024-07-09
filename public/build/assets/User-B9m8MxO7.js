import{_ as w,E as S,c as o,a as s,w as k,d as l,v as h,t as d,e as i,f,F as v,g as C,h as g,b as p,k as m,T as y,l as V,o as r}from"./app-JFC7Siur.js";const U={mixins:[S],data:function(){return{data:{},pharmacies:[],password:null,passwordFieldVisible:!1,passwordSecurityStatus:!1,code:"",authorizable:!1,passwordFieldVisible:!1,emailFieldVisible:!1,loginCodeVisible:!1,loading:!1,errors:{},userInfo}},mounted(){this.getPharmacies(),userInfo.id!=this.$route.params.id&&userInfo.role<30?this.$router.push("/notallowed"):(this.getData(),this.getPasswordSecurityStatus())},computed:{tableUrl:function(){return"/inventory/user/"+this.$route.params.id},dataUrl:function(){return"/users/"+this.$route.params.id},postUrl:function(){return"/users/"+this.$route.params.id},loginAsUrl:function(){return"/login_as/"+this.$route.params.id}},methods:{getData:function(){this.loading=!0,axios.get(this.dataUrl).then(e=>{this.data=e.data.data.userData,this.loading=!1}).catch(e=>{this.reportError(e)})},getPasswordSecurityStatus:function(e){axios.get(`/users/${this.$route.params.id}/2fa-status`).then(t=>{this.passwordSecurityStatus=t.data.data,this.passwordSecurityStatus&&axios.get(`/users/${this.$route.params.id}/2fa-code`).then(u=>{this.code=u.data.data})}).catch(t=>{this.reportError(t)})},enable2fa:function(){axios.post(`/users/${this.$route.params.id}/2fa-enable`).then(()=>{this.getPasswordSecurityStatus()}).catch(e=>{this.reportError(e)})},disable2fa:function(){axios.post(`/users/${this.$route.params.id}/2fa-disable`).then(()=>{this.getPasswordSecurityStatus()}).catch(e=>{this.reportError(e)})},getPharmacies(){axios.get("/pharmacies/list").then(e=>{this.pharmacies=e.data.data}).catch(e=>{this.reportError(e)})},togglePasswordChange:function(){this.passwordFieldVisible=!this.passwordFieldVisible},toggleEmailChange:function(){this.emailFieldVisible=!this.emailFieldVisible},toggleLoginCodeChange:function(){this.loginCodeVisible=!this.loginCodeVisible},update:function(){this.loading=!0;let e={name:this.data.name,pharmacy_id:this.data.pharmacy_id,surname:this.data.surname,email:this.data.email,role:this.data.role,code:this.data.code};this.password&&(e.password=this.password),axios.post(this.postUrl,e).then(t=>{this.postSuccess(t.data.message),this.errors={},this.loading=!1}).catch(t=>{this.errors=t.response.data.errors,this.loading=!1})},loginAs:function(){axios.get(this.loginAsUrl).then(e=>{location.reload()}).catch(e=>{console.warn(e)})},generateCode(e){for(var t="",u="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789$%()[]?!:@/",b=u.length,c=0;c<e;c++)t+=u.charAt(Math.floor(Math.random()*b));return t},storeCode(){this.data.code=this.generateCode(14)}}},_={class:"content"},B={class:"card"},z=s("div",{class:"card-header"},[s("h3",null,"User details")],-1),E={class:"card-body"},F=s("p",{class:"h4 mb-3"},"User details",-1),P={key:0,class:"invalid-feedback d-block"},A={key:1,class:"invalid-feedback d-block"},D=s("br",null,null,-1),I=s("label",null,"Role",-1),L=s("br",null,null,-1),T=s("option",{value:"5"},"Shipping",-1),M={key:0,value:"30"},N={key:1,value:"50"},$={key:2,value:"60"},G=s("br",null,null,-1),H=s("label",null,"Pharmacy",-1),R=s("br",null,null,-1),j=["value"],Q={key:2,class:"invalid-feedback d-block"},q=s("br",null,null,-1),J={style:{display:"flex","flex-direction":"column",width:"100%","align-items":"center","justify-content":"center"}},K={key:0},O={key:1},W={key:0},X=["innerHTML"],Y=s("p",null,"This QR code can be scanned by an authenticator app to start using it.",-1),Z=s("br",null,null,-1),x={key:1,class:"invalid-feedback d-block"},ee={key:0,class:"input-group mb-3"},te={class:"input-group-append",style:{display:"inline"}},se={key:1,class:"invalid-feedback d-block"},ae=s("br",null,null,-1),oe={key:1,class:"invalid-feedback d-block"},re=s("br",null,null,-1),ie=s("button",{class:"btn btnSize01 secondaryBtn",type:"submit"},"Update",-1);function ne(e,t,u,b,c,n){return r(),o("div",_,[s("section",B,[z,s("div",E,[s("form",{class:"text-center p-5",onSubmit:t[14]||(t[14]=k((...a)=>n.update&&n.update(...a),["prevent"]))},[F,l(s("input",{"onUpdate:modelValue":t[0]||(t[0]=a=>e.data.name=a),type:"text",id:"name",class:"form-control tBoxSize02 mb-10",placeholder:"Name"},null,512),[[h,e.data.name]]),e.errors.name?(r(),o("div",P,d(e.errors.name[0]),1)):i("",!0),l(s("input",{"onUpdate:modelValue":t[1]||(t[1]=a=>e.data.surname=a),type:"text",id:"surname",class:"form-control tBoxSize02 mb-10",placeholder:"Surname"},null,512),[[h,e.data.surname]]),e.errors.surname?(r(),o("div",A,d(e.errors.surname[0]),1)):i("",!0),D,I,L,l(s("select",{"onUpdate:modelValue":t[2]||(t[2]=a=>e.data.role=a),class:"browser-default custom-select mb-10"},[T,e.userInfo.role>=30?(r(),o("option",M,"Pharmacy Admin")):i("",!0),e.userInfo.role>=50?(r(),o("option",N,"Admin")):i("",!0),e.userInfo.role>=60?(r(),o("option",$,"SysAdmin")):i("",!0)],512),[[f,e.data.role]]),G,H,R,l(s("select",{"onUpdate:modelValue":t[3]||(t[3]=a=>e.data.pharmacy_id=a),class:"browser-default custom-select mb-10"},[(r(!0),o(v,null,C(e.pharmacies,a=>(r(),o("option",{key:a.PharmacyID,value:a.PharmacyID},d(a.Title),9,j))),128))],512),[[f,e.data.pharmacy_id]]),e.errors.role?(r(),o("div",Q,d(e.errors.role[0]),1)):i("",!0),q,s("div",J,[s("p",null,[g("2FA is Currently "),e.passwordSecurityStatus?(r(),o("b",K,"Enabled")):(r(),o("b",O,"Disabled")),g(" for this account.")]),e.passwordSecurityStatus?(r(),o("div",W,[s("div",{innerHTML:e.code},null,8,X),Y])):i("",!0)]),Z,p(y,{name:"fade"},{default:m(()=>[e.passwordFieldVisible?l((r(),o("input",{key:0,autocomplete:"off","onUpdate:modelValue":t[4]||(t[4]=a=>e.password=a),type:"password",name:"new-password",id:"password",class:"form-control tBoxSize02 mb-3",placeholder:"Password"},null,512)),[[h,e.password]]):i("",!0),e.errors.password?(r(),o("div",x,d(e.errors.password[0]),1)):i("",!0)]),_:1}),p(V,{name:"fade"},{default:m(()=>[s("div",null,[e.loginCodeVisible?(r(),o("div",ee,[l(s("input",{style:{margin:"0!important"},autocomplete:"off","onUpdate:modelValue":t[5]||(t[5]=a=>e.data.code=a),type:"code",name:"code",id:"code",class:"form-control tBoxSize02 mb-10",placeholder:"Login Code"},null,512),[[h,e.data.code]]),s("div",te,[s("button",{onClick:t[6]||(t[6]=a=>n.storeCode()),class:"btn btnSize01 secondaryBtn m-0 z-depth-0 waves-effect",type:"button",id:"button-addon2"},"Generate Code")])])):i("",!0),e.errors.code?(r(),o("div",se,d(e.errors.code[0]),1)):i("",!0)])]),_:1}),ae,p(y,{name:"fade"},{default:m(()=>[e.emailFieldVisible?l((r(),o("input",{key:0,"onUpdate:modelValue":t[7]||(t[7]=a=>e.data.email=a),type:"email",id:"email",class:"form-control tBoxSize02 mb-10",placeholder:"E-mail"},null,512)),[[h,e.data.email]]):i("",!0),e.errors.email?(r(),o("div",oe,d(e.errors.email[0]),1)):i("",!0)]),_:1}),re,s("div",null,[s("button",{onClick:t[8]||(t[8]=(...a)=>n.togglePasswordChange&&n.togglePasswordChange(...a)),class:"btn btnSize01 secondaryBtn",type:"button"},"Change password"),s("button",{onClick:t[9]||(t[9]=(...a)=>n.toggleEmailChange&&n.toggleEmailChange(...a)),class:"btn btnSize01 secondaryBtn",type:"button"},"Change email"),s("button",{onClick:t[10]||(t[10]=(...a)=>n.toggleLoginCodeChange&&n.toggleLoginCodeChange(...a)),class:"btn btnSize01 secondaryBtn",type:"button"},"Change login code"),e.passwordSecurityStatus?i("",!0):(r(),o("button",{key:0,onClick:t[11]||(t[11]=(...a)=>n.enable2fa&&n.enable2fa(...a)),class:"btn btnSize01 secondaryBtn",type:"button"},"Enable 2FA")),e.passwordSecurityStatus?(r(),o("button",{key:1,onClick:t[12]||(t[12]=(...a)=>n.disable2fa&&n.disable2fa(...a)),class:"btn btnSize01 secondaryBtn",type:"button"},"Disable 2FA")):i("",!0),e.userInfo.role>=50?(r(),o("button",{key:2,onClick:t[13]||(t[13]=(...a)=>n.loginAs&&n.loginAs(...a)),class:"btn btnSize01 secondaryBtn",type:"button"},"Login as user")):i("",!0),ie])],32)])])])}const de=w(U,[["render",ne]]);export{de as default};
//# sourceMappingURL=User-B9m8MxO7.js.map
