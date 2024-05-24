<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// mail function
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
// apps
use App\Entity\Worker;
use App\Form\WorkerType;

class WorkersController extends AbstractController
{
    #[Route('/workers/read', name: 'workers_read', methods: ['GET', 'HEAD'])]
    public function read(ManagerRegistry $managerRegistry): Response
    {
        $worker_repo = $managerRegistry->getRepository(Worker::class);
        $workers = $worker_repo->findAll();
        // return $this->render('workers/index.html.twig', [
        //     'controller_name' => 'WorkerController',
        //     'workers' => $workers
        // ]);
        return $this->json($workers);
    }

    #[Route('/workers/create/form', name: 'workers_create_form')]
    public function createByForm(ManagerRegistry $managerRegistry, Request $request): Response
    {
        $worker = new Worker();
        // Create a form
        $form = $this->createForm(WorkerType::class, $worker);

        // Handle form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Process the form data (e.g., save to the database, dispatch a message to a queue, etc.)
            //TODO pending to insert
            // Redirect to a success page or render a confirmation message
            $entityManager = $managerRegistry->getManager();
            $entityManager->persist($worker);
            $entityManager->flush();
            return $this->redirectToRoute('worker_success');
        }

        return $this->render('workers/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/workers/create', name: 'workers_create', methods: ['POST'])]
    public function create(ManagerRegistry $managerRegistry, Request $request): Response
    {
        // get data from request
        if ($request->isMethod('POST')) {
            // Get values directly from the POST request
            $data = json_decode($request->getContent(), true);
            $name = $data['name'];
            $surname = $data['surname'];
            $birthdate = $data['birthdate'];

            // setting worker object,
            $worker = new Worker();
            $worker->setName($name);
            $worker->setSurname($surname);
            $worker->setBirthdate(new \DateTime($birthdate));


            // persist data
            $entityManager = $managerRegistry->getManager();
            $entityManager->persist($worker);
            $entityManager->flush();
        }
        return $this->json($worker);
    }

    #[Route('/index', name: 'index')]
    public function index(): Response
    {
        return $this->render('base.html.twig');
    }

    #[Route('/sendMail', name: 'sendMail')]
    public function sendMail(): Response
    {
        // Create a Transport object
        $transport = Transport::fromDsn('smtp://laravelhospital@gmail.com:ymbtzlauakebizlr@smtp.gmail.com:587');

        // Create a Mailer object
        $mailer = new Mailer($transport);

        // Create an Email object
        $email = new Email();

        // Set the "From address"
        $email->from('symfony@gmail.com');

        // Set the "To address"
        $email->to(
            'jose.portugal.ortuno@gmail.com'
            # 'email2@gmail.com',
            # 'email3@gmail.com'
        );

        // Set "CC"
        # $email->cc('cc@example.com');
        // Set "BCC"
        # $email->bcc('bcc@example.com');
        // Set "Reply To"
        # $email->replyTo('fabien@example.com');
        // Set "Priority"
        # $email->priority(Email::PRIORITY_HIGH);

        // Set a "subject"
        $email->subject('A Cool Subject!');

        // Set the plain-text "Body"
        $email->text('The plain text version of the message.');

        // Set HTML "Body"
        $email->html('
    <h1 style="color: #fff300; background-color: #0073ff; width: 500px; padding: 16px 0; text-align: center; border-radius: 50px;">
    The HTML version of the message.
    </h1>
    <img src="cid:Image_Name_1" style="width: 600px; border-radius: 50px">
    <br>
    <img src="cid:Image_Name_2" style="width: 600px; border-radius: 50px">
    <h1 style="color: #ff0000; background-color: #5bff9c; width: 500px; padding: 16px 0; text-align: center; border-radius: 50px;">
    The End!
    </h1>
    ');

        // Add an "Attachment"
        //$email->attachFromPath('example_1.txt');
        //$email->attachFromPath('example_2.txt');

        // Add an "Image"
        //$email->embed(fopen('image_1.png', 'r'), 'Image_Name_1');
        //$email->embed(fopen('image_2.jpg', 'r'), 'Image_Name_2');

        // Sending email with status
        try {
            // Send email
            $mailer->send($email);

            // Display custom successful message
        } catch (TransportExceptionInterface $e) {
            return $this->render('workers/email.html.twig', ['message' => 'error sending email']);
        }
        return $this->render('workers/email.html.twig', ['message' => 'email was sent successfully']);
    }

    #[Route('/worker/success', name: 'worker_success')]
    public function success(): Response
    {
        return $this->render('workers/success.html.twig', ['message' => 'worker added']);
    }
}
