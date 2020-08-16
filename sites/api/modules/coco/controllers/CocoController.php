<?php

namespace Controllers;


use Custom\Models\Alerts;
use Custom\Models\Cases;
use Custom\Models\Damage;
use Custom\Models\Deliveries;
use Custom\Models\GeoObjects;
use Custom\Models\History;
use Custom\Models\Notifications;
use Custom\Models\Objects;
use Custom\Models\Pallet;
use Custom\Models\TimeRecords;
use Custom\Models\TrackingStatistics;
use Custom\Models\TransactionItems;
use Custom\Models\Transactions;
use Custom\Models\UserVehicles;
use Custom\Models\Vehicles;
use Custom\Models\WorkTimeExceptions;
use Models\Activities;
use Models\Parameters;

class CocoController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {
        set_time_limit(0);
        ini_set( 'memory_limit' , '999999999M' );

        $logs = [];
        $logs[] = Pallet::updateUserId(Pallet::class, ['employee_id','creator_id']);

        $logs[] = WorkTimeExceptions::updateUserId(WorkTimeExceptions::class, ['creator_id','user_id']);

        $logs[] = UserVehicles::updateUserId(UserVehicles::class, ['creator_id', 'deleter_id','user_id']);
        $logs[] = Damage::updateUserId(Damage::class, ['deleter_id', 'creator_id']);
        $logs[] = Deliveries::updateUserId(Deliveries::class, ['user_id', 'creator_id', 'employee_id']);
        $logs[] = Vehicles::updateUserId(Vehicles::class, ['creator_id', 'deleter_id']);
        $logs[] = GeoObjects::updateUserId(GeoObjects::class, ['user_id', 'deleter_id']);
        $logs[] = Notifications::updateUserId(Notifications::class, ['users','owner_id']);
        $logs[] = Activities::updateUserId(Activities::class, ['user_id']);
        $logs[] = Alerts::updateUserId(Alerts::class, ['user_id']);
        $logs[] = Objects::updateUserId(Objects::class, ['owner_id','users']);
        $logs[] = Parameters::updateUserId(Parameters::class, ['deleter_id']);
        $logs[] = Transactions::updateUserId(Transactions::class, ['parent_id']);
        $logs[] = TransactionItems::updateUserId(TransactionItems::class, ['deleter_id']);
        $logs[] = TrackingStatistics::updateUserId(TrackingStatistics::class, ['users']);

        $logs[] = TimeRecords::updateUserId(TimeRecords::class, ['creator_id','employee_id']);

        return json_encode([
            "status" => "success",
            "description" => $logs,
        ]);


    }
}