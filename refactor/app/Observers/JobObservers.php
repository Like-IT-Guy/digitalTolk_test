<?php
  
namespace App\Observers;
  
use App\Models\Job;
use DTApi\Repository\BookingRepository;

class JobObserver
{
  
    /**
     * Handle the Job "created" event.
     *
     * @param  \App\Models\Job  $job
     * @return void
     */
    public function creating(Job $job)
    {
        // $job->slug = \Str::slug($job->name);
    }
  
    /**
     * Handle the Job "created" event.
     *
     * @param  \App\Models\Job  $job
     * @return void
     */
    public function created(Job $job, Request $request)
    {
        $cuser->__authenticatedUser;
        $job->job_for = array();
            if ($job->gender != null) {
                if ($job->gender == 'male') {
                    $job->job_for = 'Man';
                } else if ($job->gender == 'female') {
                    $job->job_for = 'Kvinna';
                }
            }
            if ($job->certified != null) {
                if ($job->certified == 'both') {
                    $job->job_for = 'normal';
                    $job->job_for = 'certified';
                } else if ($job->certified == 'yes') {
                    $job->job_for = 'certified';
                } else {
                    $job->job_for = $job->certified;
                }
            }

            $request->customer_town = $cuser->userMeta->city;
            $request->customer_type = $cuser->userMeta->customer_type;
            Event::fire(new JobWasCreated($job, $request->all(), '*'));

           $this->sendNotificationToSuitableTranslators($job->id, $request->all(), '*');// send Push for New job posting

           //Send Emails
           $email = !empty($job->user_email) ? $job->user_email : $user->email;
            $name = !empty($job->user_email) ? $user->name : $user->name;
            $subject = 'Vi har mottagit er tolkbokning. Bokningsnr: #' . $job->id;
            $send_data = [
                'user' => $user,
                'job'  => $job
            ];
            $this->mailer->send($email, $name, $subject, 'emails.job-created', $send_data);

            $data = $this->jobToData($job);
            Event::fire(new JobWasCreated($job, $data, '*'));

            if($job->status == "completed") {
                $user = $job->user()->get()->first();
                if (!empty($job->user_email)) {
                    $email = $job->user_email;
                } else {
                    $email = $user->email;
                }
                $name = $user->name;
                $subject = 'Information om avslutad tolkning för bokningsnummer # ' . $job->id;
                $session_explode = explode(':', $job->session_time);
                $session_time = $session_explode[0] . ' tim ' . $session_explode[1] . ' min';
                $mail_data = [
                    'user'         => $user,
                    'job'          => $job,
                    'session_time' => $session_time,
                    'for_text'     => 'faktura'
                ];
                $mailer = new AppMailer();
                $mailer->send($email, $name, $subject, 'emails.session-ended', $mail_data);
            }
    }
  
    /**
     * Handle the Job "updated" event.
     *
     * @param  \App\Models\Job  $job
     * @return void
     */
    public function updated(Job $job)
    {
        $this->sendNotificationByAdminCancelJob($job->id);
    }
  
    /**
     * Handle the Job "deleted" event.
     *
     * @param  \App\Models\Job  $job
     * @return void
     */
    public function deleted(Job $job)
    {
          
    }
  
    /**
     * Handle the Job "restored" event.
     *
     * @param  \App\Models\Job  $job
     * @return void
     */
    public function restored(Job $job)
    {
          
    }
  
    /**
     * Handle the Job "force deleted" event.
     *
     * @param  \App\Models\Job  $job
     * @return void
     */
    public function forceDeleted(Job $job)
    {
          
    }
}