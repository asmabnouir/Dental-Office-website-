<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Spatie\GoogleCalendar\Event;
use Google_Client;
use Google_Service_Calendar;
class gCalendarController extends Controller
{
  public function indexAll(){
    
    $events = Event::get();
    return $events;
  }

  public function index(){
    //get all events
    $events = Event::get();
    //get all events with start dateTime
      $eventNameList=array();
    foreach ($events as $event){
      $startTime = $event->start->dateTime;
      $utc_date = $startTime;
      $timestamp = strtotime($utc_date);
      $date =new \DateTime();
      $date->setTimestamp($timestamp);
      $date->setTimezone(new \DateTimeZone('Europe/Paris'));
      $Startdate= $date->format('d/m/Y') ;
      $StartTime= $date->format('H:i') ;
      if ($event->transparency == 'transparent'){
        $eventNameList[] =  ["id"=>$event->id, "event_date" => $Startdate, "start_time" => $StartTime, "user_id"=> 0, 'dateTime'=>$Startdate. ' '.$StartTime];
      }else{
        $eventNameList[] =  ["id"=>$event->id, "event_date" => $Startdate, "start_time" => $StartTime, "user_id"=> $event->summary, 'dateTime'=>$Startdate. ' '.$StartTime];
      };
    }
    return $eventNameList;
   //return $events;
  }
  public function getFreeEvents(){
    $events = Event::get();
    $freeEvents=array();
    foreach ($events as $event){
        if($event->transparency == "transparent"){
            $freeEvents[] = $event->start->dateTime; 
        };
       }
       return response()->json($freeEvents);
  }

  public function createGEvent($DateTime){
    $event = new Event;
    $event->name = 'Libre';
    $event->colorId = 4 ;
    
    $event->startDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $DateTime, 'Europe/Paris');
    $event->endDateTime=  Carbon::createFromFormat('Y-m-d H:i:s', $DateTime, 'Europe/Paris')->addMinute(30);
   /* $event->startDateTime = $DateTime;
    $event->endDateTime = Carbon::now()->addMinute(30);*/
    $event->transparency = 'transparent';
    $event->save();
  }

  public function Find_g_EventByDatetTime($dateTime){
    $events = Event::get();
    $e=null;
    foreach ($events as $event){
      //convertir le start dateTime en format Y-m-d H:i:s
      $startTime = $event->start->dateTime;
      $utc_date = $startTime;
      $timestamp = strtotime($utc_date);
      $date =new \DateTime();
      $date->setTimestamp($timestamp);
      $date->setTimezone(new \DateTimeZone('Europe/Paris'));
      $startTime = $date->format('Y-m-d H:i:s');
      //  trouver l'id de l'event slectionné 
      if( $startTime == $dateTime){ 
          $e = $event->id;
        };
       }
       return $e ;
       
  }
  public function findEventById($eventId){
    $event = Event::find($eventId);
    return $event;
  }
  public function gEventDelete($eventId){
    $event = Event::find($eventId);
    $event->delete();
  }

  public function select_gEevent($eventId, $userId){
     $event = Event::find($eventId);
     //edit the event
     $event->transparency = null;
     $event->summary = $userId ;
     $event->colorId = 5 ;
     $event->save();
   }

   public function unselect_gEevent($dateTime){
    //call the event by start time
     $eventId= $this->Find_g_EventByDatetTime($dateTime);
     $event = Event::find($eventId);
     //edit the event 
     $event->transparency = 'transparent';
     $event->summary = "Libre";
     $event->colorId = 4 ;
     $event->save();
   }

}
