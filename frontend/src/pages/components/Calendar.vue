<template>
<div>
  <div class="calendar">
      <div v-if="$store.state.windowWidth <500"  class="screenHider" >
      <div ><p> Pour une meilleure expérience 
        <br>
       Tournez votre appareil avec affichage en mode paysage </p>
        </div>
      </div>
    <div>
      <header>
      <button @click.prevent="getToday()" class="secondary" style="align-self: flex-start; flex: 0 0 1">Today</button>
      <div class="calendar__title" style="display: flex; justify-content: center; align-items: center">
        <div @click.prevent="down()" class="icon secondary chevron_left">‹</div>
        <h1 class="" style="flex: 1;"><span></span><strong>{{calendarWeek.weekRange}}</strong> </h1>
        <div @click.prevent="up()" class="icon secondary chevron_left">›</div>
      </div>
      <div style="align-self: flex-start; flex: 0 0 1"></div>
  </header>
    <div class="outer">
    <table>
    <thead>
      <tr>
        <th class="headcol"></th>
        <th  v-for="(day, index) in weekdays" :key="index">{{day}} {{calendarWeek.weekDates[index].getDate()}}</th>
      </tr>
    </thead>
    </table>
    </div>

    <div class="wrap">
  <table>
    <tbody v-if="$store.getters.role === 'admin'">
      <tr v-for="(time, index) in times" :key="index">
      <td class="headcol">{{time}}</td>
      <td v-for="(dayDate, index) in calendarWeek.weekDates" :key="index">
       <div v-if="displayEventA(dayDate, time)" 
        v-bind:class="['event' ,  ((typeof displayEventA(dayDate,time)!== 'undefined')? displayEventA(dayDate,time).user_id : '') !== 0 ? 'event_selected' : '']"
        @click="
        edit(((typeof displayEventA(dayDate,time)!== 'undefined')? displayEventA(dayDate,time).user_id : ''), displayEventA(dayDate,time).id)">
          <p v-bind:class="[$store.state.windowWidth <700? 'mobile':'']"> {{((typeof displayEventA(dayDate,time)!== 'undefined')? displayEventA(dayDate,time).user_id : '') !== 0 ? 'Cliquez ici pour supprimer ce rdv' : 'Cliquez ici pour editer le rdv'}} </p>
        </div>
        <div v-else class="empty" @click="createEvent(dayDate, time,$event)">
        </div>
      </td>
    </tr>
              <!-- small modal -->
            <modal
              :show.sync="modals.mini"
              class="modal-primary"
              :show-close="true"
              headerClasses="justify-content-center"
              type="mini"
            >
               <n-button type="default" size="lg" style=" width:100%;" @click="deleteEvent(modals.eventId)" >
                 Delete
               </n-button>
               <br>
               <n-button type="default" size="lg" style=" width:100%;" @click="modals.input=true">
                 Add User
               </n-button>
               <fg-input 
                v-model="search"
                :style="displayInput"
                placeholder="Rechercher le user"
                addon-right-icon="now-ui-icons ui-1_zoom-bold"
                ></fg-input>
               <div :style="displaySearch" >
                  <div  id="userSearch" v-for="(user,index) in filteredUsers" :key="index" @click="eventAddUser(user), search=''">
                     {{user.name}}</div>
              </div>
            </modal>
    </tbody>
    <tbody v-else>
      <tr v-for="(time, index) in times" :key="index">
      <td class="headcol">{{time}}</td>
      <td v-for="(dayDate, index) in calendarWeek.weekDates" :key="index">
       <div v-show="displayEventC(dayDate, time)"
        v-bind:class="['event' ,  ((typeof displayEventC(dayDate,time)!== 'undefined')? displayEventC(dayDate,time).user_id : '') !== 0 ? 'event_selected' : '']"
        @click.prevent=" !$store.state.token ? $router.push({ name: 'login'}) : toggleSelect(dayDate, time,$event)" >
          <p v-bind:class="[$store.state.windowWidth <700? 'mobile':'']"> {{((typeof displayEventC(dayDate,time)!== 'undefined')? displayEventC(dayDate,time).user_id : '') !== 0 ? "Cliquez ici pour annuer le rdv" : "Cliquez ici pour réserver le rdv"}}</p>
        </div>
      </td>
    </tr>
    </tbody>
  </table>
    </div>
      </div>
</div>
  </div>
</template>
<script>
import axios from 'axios';
import { Button, Modal,FormGroupInput, } from '@/components';
    export default {
        //
      name: 'calendar',
      components: {
      Modal,
    [Button.name]: Button,
    [FormGroupInput.name]: FormGroupInput,
      },
      data() {
        return {
        search:'',
        error:'',
        weekdays: ['Lu','Ma','Me','J','Ve','Sa','Di'],
        times:[],
        calendarWeek: {
          weekNo: 0,
          weekRange: '',
          startDate: 0,
          weekDates:[],
        },
        startWeek:0,
        events:[],
        selected:false,
        users:[],
        modals: {
        mini: false,
        input:false,
        eventId:0,
        search : false,
      },
      }
      },
      computed: {
        displayInput(){
         return  this.modals.input==true ? 'display : block' : 'display :none';
        },
        filteredUsers(){
         return this.users.filter((user)=>{
            return user.name.match(this.search)
         })
        },
        displaySearch(){
          return this.search.length>=1 ? 'display:block':'display:none'
        },
        windowWidth() {
          return this.$store.state.windowWidth;
        }

      },
    methods: {
      ///////////////////////////////////////////// calendar display ////////////////////////////////////
      
      generateTimes(x,hs,he){
      // x = minutes interval
      // hs = start hour
      // he= end time
        var times = []; // time array
        var tt = hs*60; // start time

        //loop to increment the time and push results in array
        for (var i=0;tt<he*60; i++) {
          var hh = Math.floor(tt/60); // getting hours of day in 0-24 format
          var mm = (tt%60); // getting minutes of the hour in 0-55 format
          times[i] = ("0" + (hh % 24)).slice(-2) + ':' + ("0" + mm).slice(-2) ; // pushing data in array 
          tt = tt + x;
        }
        this.times=times;
      },

      /////////////////////////////////////////////  calendar dates  ////////////////////////////////////

      //formatter les dates
        formatDate(date){
          return new Intl.DateTimeFormat('fr').format(date)
        },
        formatToPhp(date){
         return date.toString().split("/").reverse().join("-");
        },
       //get the week number
      CurrentWeek(){
      var date = new Date();
      date.setHours(0, 0, 0, 0);
      // Thursday in current week decides the year.
      date.setDate(date.getDate() + 3 - (date.getDay() + 6) % 7);
      // January 4 is always in week 1.
      var week1 = new Date(date.getFullYear(), 0, 4);
      // Adjust to Thursday in week 1 and count number of weeks from date to week1.
      var res =  1 + Math.round(((date.getTime() - week1.getTime()) / 86400000
                            - 3 + (week1.getDay() + 6) % 7) / 7);
      this.calendarWeek.weekNo= res;
      this.startWeek = res; 
      return res;
      },
      CurrentWeekYear(){
       var date = new Date();
        date.setDate(date.getDate() + 3 - (date.getDay() + 6) % 7);
        return date.getFullYear();
      },
      //get a range of the weekNo date from monday date to sunday
      getDateRangeOfWeek(){
        var startDay = 1;
        let y = this.CurrentWeekYear();
        let w =  this.calendarWeek.weekNo;
        let weekStart = new Date(y, 0, (1 + (w - 1) * 7));
         weekStart.setDate(weekStart.getDate() + (startDay - weekStart.getDay())); // 0 - Sunday, 1 - Monday etc
        let weekEnd = new Date(weekStart.valueOf() + 6*86400000); //add 6 days to get last day
        this.calendarWeek.startDate = weekStart; //console.log(new Intl.DateTimeFormat('fr').format(date));
        this.calendarWeek.weekRange = this.formatDate(weekStart)+ ' à '+ this.formatDate(weekEnd);
        return this.formatDate(weekStart),  this.formatDate(weekEnd);
        },
        //get weeks dates
        getWeekDates(){
        this.calendarWeek.weekDates = [];
        let dStart= this.calendarWeek.startDate ;
        this.weekdays.forEach((d, i) =>
        //86400000 (1000*60*60*24) - number of milliseconds in one day:
        { var date = new Date( dStart.valueOf() +i*86400000);
         this.calendarWeek.weekDates.push(date);
         console.log(date)
        return date;
        });
        },
        //rebuild the calendar depending on the currentweek saved in data
        displayCalendar(){
            this.getDateRangeOfWeek();
            this.getWeekDates();
        },

      //moving in calendar
      up(){
        this.calendarWeek.weekNo +=1;
        this.displayCalendar();
        },
      down(){
        if (this.startWeek<this.calendarWeek.weekNo) {
          this.calendarWeek.weekNo -=1 ;
          this.displayCalendar();
        }
      },
      getToday(){
      this.CurrentWeek();
      this.displayCalendar();
      },
      //get events
      getEvents(){
        axios.get('http://localhost:8000/api/event/index',  {
        }).then(response=>{
          this.events = response.data
          console.log(this.events);
        }).catch(error=>{
        console.log(error.message);
        });
      },
       ///////////////////////////////////////////// calendar Events Client side  ////////////////////////////////////
      //display Events
      displayEventC(dayDate, time) {
      if ( this.$store.state.token ) {
        let user_ev = this.events.find(el=>{
           if( el.event_date === this.formatDate(dayDate) && el.start_time === time  && (el.user_id === 0 || el.user_id == this.$store.state.userId) ){
           return el
          }
          });
        if(typeof user_ev  !== 'undefined'){
          return user_ev
        }
      }
      else{
      let user_ev= this.events.find(el=>{
          if( el.event_date === this.formatDate(dayDate) && el.start_time === time && el.user_id === 0 ){
          return el
          }
          });
          if(typeof user_ev  !== 'undefined'){
          return user_ev
        }
      }
    },
      //working on class
      toggleClass($event, className){
         $event.target.classList.toggle(className);
      },
      addCalss($event, className){
      $event.target.classList.add(className);
      },
      removeClass($event, className){
      $event.target.classList.remove(className);
      },
      toggleSelect(dayDate, time,$event){
        //select an event
        //add ths id of the current_user to event.user_id 
       let  currentEvent_id= this.displayEventC(dayDate, time).id;
        if ( this.displayEventC(dayDate, time).user_id ===0) {
          if (confirm("Voulez vous confirmez votre rdv du" + this.formatDate(dayDate) + " à " + time) === true) {
            axios.post('http://localhost:8000/api/event/client/select',{
              id : currentEvent_id,
              date: this.formatToPhp(this.formatDate(dayDate)),
              time:  time,
              token:  this.$store.state.token
            }).then(response=>{
              this.getEvents();
            }).catch(error=>{
            if (error.message == 'Request failed with status code 401') {
              this.$router.push({ name: 'login'});
            }
            }); 
          }
        }
        //unselect an event
        //put event.user_id to 0
        else {
          if (confirm("Voulez vous annuler votre rdv du" + this.formatDate(dayDate) + " à " + time) === true) {
            axios.post('http://localhost:8000/api/event/client/unselect',{
              id : currentEvent_id,
              token:  this.$store.state.token
            }).then(response=>{
              this.getEvents();
            }).catch(error=>{
              console.log(error.message)
              });
          }
        }
      },
          ///////////////////////////////////////////// calendar Events Admin side  ////////////////////////////////////
      //get Users list 
          getUsers(){
        axios.post('http://localhost:8000/api/users/list', {
          token : this.$store.state.token
        }).then(response=>{
          this.users = response.data
         console.log(this.users);
        }).catch(error=>{
        console.log(error.message);
        });
          },
        eventAddUser(user){
       //add the user.id to event_user_id
       console.log(user.id);
       console.log(this.modals.eventId);
       this.search = user.name
        axios.post('http://localhost:8000/api/event/addUser',{
        userId : user.id,
        id : this.modals.eventId,
        token:  this.$store.state.token
       }).then(response=>{
         this.modals.mini = false;
        this.getEvents();
       }).catch(error=>{
         if (error.message == 'Request failed with status code 401') {
           this.$router.push({ name: 'login'});
         }
        });
          },

            //display Events
          displayEventA(dayDate, time) {
          let ev = this.events.find(el=>{
              if( el.event_date === this.formatDate(dayDate) && el.start_time === time ){
              return el
              }
              });
            if(typeof ev !== 'undefined'){
              return ev
            }
      },

      //create free event
    createEvent(dayDate, time, $event){
      console.dir("this is from js : "+ this.formatToPhp(dayDate));
      axios.post('http://localhost:8000/api/event/create',  {
        date: this.formatToPhp(this.formatDate(dayDate)),
        time: time,
        token:  this.$store.state.token
        }).then(response=>{
          console.dir(response.data);
          this.getEvents();
        }).catch(error=>{
        console.log(error.message);
        });
    },
    deleteEvent(eventId){
      axios.post('http://localhost:8000/api/event/delete',  {
        id : eventId,
        token:  this.$store.state.token
        }).then(response=>{
        this.getEvents();
        }).catch(error=>{
        console.log(error.message);
        });
        this.modals.mini=false;
    },
    edit(eventUserId, eventId){
        if (eventUserId === 0) {
          this.getUsers(),
          this.modals.mini=true,
          this.modals.eventId= eventId
        } else {
         this.deleteEvent(eventId) 
        }
      },
    },
    /////////////////////////////////////////////  calendar build  ////////////////////////////////////
    created() {
    //build the times slots
    this.generateTimes(30,8,17);
    this.getToday();
    this.getEvents();
    },
    };
    </script>

<style lang="scss" scoped>


* {
  margin: 0;
  border: 0;
}
.screenHider{
  z-index: 100;
  width :100% ;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.603);
  position: absolute; 
  div{
    position: absolute;
    padding: 30px;
    background-color: #e8e8e8;
    top: 50%;
    left: 50%;
    transform: translateX(-50%) translateY(-50%);
    p{
      text-align: center;
      color:#24549E ;
    }
  }

}
.outer {
  position:relative;
}

.calendar {
  .mobile{
  font-size: 70%;
}
  position:relative;
  margin: 20px auto;
  max-width: 1280px;
  min-width: 500px;
  box-shadow: 0px 30px 50px rgba(0, 0, 0, 0.2) ,0px 3px 7px rgba(0, 0, 0, 0.1);
}

.wrap {
  overflow-y: hidden;
  overflow-x: hidden;
  max-width: 1280px;
  margin-top:40px;
}

thead {
    box-shadow: 0px 2px 3px rgba(0, 0, 0, 0.2);
}

thead th {

  text-align: center;
  width: 100%;

}

header {
  background: #fff;
  padding: 1rem;
  color: rgba(0, 0, 0, 0.7);
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
  position: relative;
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  border-radius: 8px 8px 0px 0px;
}

header h1 {
 font-size: 1.25rem;
 text-align: center;
 font-weight: normal;

}
tbody {
  position: relative;
}
table {
  background: #fff;
  width: 100%;
  height: 100%;
  border-collapse: collapse;
  table-layout: fixed;

}



.headcol {
  width: 60px;
  font-size: 0.875rem;
  font-weight: 500;
  color: rgba(0, 0, 0, 0.5);
  padding: 0.25rem 0;
  text-align: center;
  border: 0;
  position: relative;
  top: -12px;
  border-bottom: 1px solid transparent;
}

thead th {
  font-size: 1rem;
  font-weight: bold;
  color: rgba(0, 0, 0, 0.9);
  padding: 1rem;
}

thead {
    z-index: 2;
    background: white;
    border-bottom: 2px solid #ddd;

}

tr, tr td {
  height: 20px;
}
td {
  text-align: center;
}
tr:nth-child(odd) td:not(.headcol) {
  border-bottom: 1px solid #e8e8e8;
}

tr:nth-child(even) td:not(.headcol) {
  border-bottom: 1px solid #eee;
}

tr td {
  border-right: 1px solid #eee;
  padding: 0;
  white-space: none;
  word-wrap: nowrap;
}

tbody tr td {
  position: relative;
  vertical-align: top;
  height: 40px;
  padding: 0.25rem 0.25rem 0 0.25rem;
  width: auto;

}

.secondary {
  color: rgba(0, 0, 0, 0.4);
}
.empty{
  height:100%;
  background-color: rgba(242, 225, 245, 0.719) ;
}

.event_selected{
  background:  #eeef20 ! important  ; // rgb(252, 134, 0)! important;
  color : rgba(0, 0, 0, 0.9) ! important ;
}

.event{
  
 background:  #7ac74f ;  //#24549E;
  color: white;
  border-radius: 2px;
  text-align: left;
  font-size: 0.875rem;
  z-index: 2;
  padding: 0.5rem;
  overflow-x: hidden;
  transition: all 0.2s;
  cursor: pointer;
}

.event:hover {
  box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2);
}
.empty:hover:after {
  content: "+";
  vertical-align: middle;
  font-size: 1.875rem;
  font-weight: 100;
  color: rgba(0, 0, 0, 0.5);
  position: absolute;
}

button.secondary {
  border: 1px solid rgba(0, 0, 0, 0.1);
  background: white;
  padding: 0.5rem 0.75rem;
  font-size: 14px;
  border-radius: 2px;
  color: rgba(0, 0, 0, 0.5);
  box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  font-weight: 500;
}

button.secondary:hover {
  background: #fafafa;
}
button.secondary:active {
  box-shadow: none;
}
button.secondary:focus {
  outline: 0;
}

.today {
  color: red;
}

.now {
  box-shadow: 0px -1px 0px 0px red;
}

.icon {
  font-size: 1.5rem;
  margin: 0 1rem;
  text-align: center;
  cursor: pointer;
  vertical-align: middle;
  position: relative;
  top: -2px;
}

.icon:hover {
  color: red;
}
//search users list 
#userSearch{
    text-align: center;
    margin: 15px auto;
  cursor: pointer;
}
#userSearch:hover{
  color:#24549E;
}
</style>

