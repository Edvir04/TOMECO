<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Violation;
use Illuminate\Support\Facades\DB;

class ViolationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $violations = [
            // Additional violations from checklist (shown first)
            ['name' => 'Driving without D/L', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Unregistered Vehicle', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Illegal Parking', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Disregarding Traffic Sign', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Obstruction', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Truck Ban', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Operating Along National Highway', 'price' => 500.00, 'is_active' => true],
            ['name' => 'No Helmet', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Defective Head Light', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Violation to CO # 2007-10-31 "The Anti-Littering Ordinance"', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Violation to CO # 2009-10-160 "The Anti-Smoking Ordinance."', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Violation to CO # 2007-10-66 "The anti-urinating and Defecating Ordinance."', 'price' => 500.00, 'is_active' => true],
            
            // Section violations (Sec. 1-73)
            ['name' => 'Sec. 1: Failure to give right of way to Police and other emergency vehicles giving audible signals.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 2: Allowing passengers to ride on board or, hitch to one\'s vehicle.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 3: Driving or parking on a side walk.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 4: Obscure or dirty plate number.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 5: Defective headlights, taillights, stop lights, wiper and other accessories.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 6: Failure to give the necessary signal when starting or stopping.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 7: Illegal Parking.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 8: Failure to carry the official receipt of registration of the current year.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 9: Operating an unsafe, unsightly or dilapidated vehicle.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 10: Unauthorized use of improvised plates.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 11: Driving a vehicle with passengers in excess of capacity.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 12: Driving a vehicle with horn that emits exceptionally loud and startling or disagreeable sound.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 13: Driving a vehicle with a defective brake system.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 14: Driving a freight or cargo vehicle loaded in excess of authorized capacity.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 15: Driving vehicle recklessly.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 16: Obstructing or impeding the passage of other vehicles, loading and unloading of passengers at intersections or within prohibited areas.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 17: Driving with unsigned license.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 18: Driving with invalid or delinquent driver license.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 19: Driving a vehicle with a delinquent suspended or invalid registration or without the proper license plate for the current year of registration.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 20: Driving without first securing a driver\'s license.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 21: Driving without carrying a driver\'s license with him.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 22: Using or attempting to use a fake license, identification card, registration, certificate, vehicle plate number, and tag or sticker.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 23: Falsely or fraudulently representing as valid and enforced a delinquent suspended or revoked license.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 24: Using a vehicle registered for private use as that for hire or allowing another person to use the driver\'s license of the authorized or real driver of the vehicle.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 25: Cutting corners of blind curbs.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 26: Making U-Turn on the approach or on top of the bridge or elsewhere but not at intersection.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 27: Overtaking or passing on curb, at intersection and approaches of bridge, bill and along places where overtaking is prohibited.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 28: Coming out of Side Street or driveways without precautions.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 29: Vehicle racing on roads or streets.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 30: Failure to stop on entering a thru-street.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 31: Failure to consider proper clearance when overtaking.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 32: Failure to observe the right-hand rule to yield the right-of-way at highway intersection.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 33: Driving on a wrong side of the street.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 34: Backing against the flow of traffic.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 35: Turning from wrong lane.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 36: Driving without lights during the hours prescribed by law.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 37: Driving or crossing the safety island not intended for motor vehicle.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 38: Disregarding automatic signaling devices, lights or any traffic signal, sign or makings.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 39: Failure to stop or slow down on crosswalk or pedestrian lanes with or without pedestrians crossing.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 40: Over-speeding or fast driving.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 41: Failure to slow down on school zones, hospital zones, churches, courtrooms and the likes.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 42: Entering a "DO NOT ENTER" sign.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 43: Disregarding a "NO LEFT TURN" sign.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 44: Passing a "THRU-RED LIGHT".', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 45: Allowing passengers in excess of the capacity of the front seat.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 46: Loading or unloading passengers within the prohibited zone.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 47: Soliciting passengers at street corners.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 48: Loading and unloading passenger in the middle of the road.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 49: Loading and unloading passenger at intersection.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 50: Parking a vehicle or permit the same to stand attended or unattended upon a highway.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 51: Driving a vehicle with open muffler or making unnecessary noise.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 52: Failure to display a red flag or red light at the rear end of the load which extends beyond the projected length of the vehicle.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 53: Driving a vehicle emitting excessive smoke.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 54: Driving along a highway without proper permit for motor vehicles with metallic tires.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 55: Operating a service vehicle without a commercial or trade name and the words "NOT FOR HIRE" painted in both sides of the motor vehicle.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 56: Driving a motor truck without capacity marking plainly lettered on both sides of the motor vehicle.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 57: Driving a vehicle with a broken windshield.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 58: Driving a motor vehicle with a red light or halogen lamp forward or overhead of the same.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 59: Driving with inappropriate driver\'s license or conductor\'s license.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 60: Refusal to show or surrender the driver\'s license and/or conductor\'s license.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 61: Operating a vehicle loaded with soil, sand, gravel, stones and the likes without canvass covering.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 62: Operating a motor vehicle equipped with an unauthorized siren.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 63: Driving while under the influence of liquor or narcotics drugs.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 64: Failure to carry the conductor\'s license.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 65: Serving as conductor without first securing a conductor\'s permit.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 66: Carrying freight or cargo in excess of the registered net carrying capacity.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 67: Hostile or arrogant attitude of a driver or conductor towards a lawful Authority or improper conduct or behavior like bribery and other similar offenses tending to corrupt a police officer including discourteous to passenger.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 68: Transferring, lending or otherwise allowing any person to use his driver\'s license for the purpose of enabling such person to operate a motor vehicle.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 69: Engaging, Employing or hiring any person to operate a motor vehicle other than a duly license professional driver.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 70: Operating in a prohibited route.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 71: Constructing structures, edifices or stand that may obstruct the free passage of pedestrians with the side walk.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 72: Refusal to convey passenger or having agreed to convey the same, negligently, culpably or unreasonably failed to convey said passenger to his place or destination.', 'price' => 500.00, 'is_active' => true],
            ['name' => 'Sec. 73: To demand and collect a fare more than the existing rate as authorized by law, rules and regulations.', 'price' => 500.00, 'is_active' => true],
        ];

        // Insert or update violations (updateOrCreate prevents duplicates)
        $created = 0;
        $updated = 0;
        foreach ($violations as $violation) {
            $existing = Violation::where('name', $violation['name'])->first();
            if ($existing) {
                $existing->update($violation);
                $updated++;
            } else {
                Violation::create($violation);
                $created++;
            }
        }

        $this->command->info('Successfully processed ' . count($violations) . ' violations. Created: ' . $created . ', Updated: ' . $updated . '.');
    }
}

