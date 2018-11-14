<?php

namespace Marie\LouvresBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Marie\LouvresBundle\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Marie\LouvresBundle\Entity\ticket;

class BookingManager
{
    private $session;


    //créez une __construct()méthode avec un $sessionargument qui possède l'indicateur de type (chemin d'accès). Définissez cette $sessionpropriété sur une nouvelle propriété et utilisez-la plus tard
    public function __construct( SessionInterface $session) //EntityManagerInterface $em) // injecter l'entity manager
    {
        $this->session = $session;
        //$this->em = $entityManager;
    }

    // mettre le chemin de l'entité permet de typer la variable = c'est un objet de type reservation
    public function initBooking(\Marie\LouvresBundle\Entity\reservation $reservation)
    {
        $this->session->set('reservation', $reservation);

    }



    public function getReservation()
    {

        //verifier si reservationId existe si il existe je fais un find dans la bdd via doctrine
        // sil n'existe pas :

            return $this->session->get('reservation');
    }

    public function updateBooking(\Marie\LouvresBundle\Entity\reservation $reservation)
    {
        // recupérer les billets
        $tickets = $reservation->getTickets();
        //calcul prix
        $total = $this->calculPriceTotal($tickets);
        //par defaut payement à false
        $payment = $reservation->getPayment()=== false;
        // ---------------------------------------- le code du billet revoir comment faire pour faire un code complet
        $code = $reservation->getNumberofticket().$reservation->getName().date("YmdHis");//streplace pour enlever les espaces
        var_dump($code);

        $reservation->setPrice($total)->setPayment($payment)->setCode($code);
        // On boucle sur les tickets pour les lier à la reservation
        foreach ($tickets as $ticket){$reservation->addTicket($ticket);}
        return $reservation;

    }
    public function paiementBooking(\Marie\LouvresBundle\Entity\reservation $reservation)
    {
        $payment = $reservation->getPayment() === true;
        $reservation->setPayment($payment);
        return $reservation;
    }

    public function calculPriceTotal($tickets)
    {
        $total = 0;
        foreach($tickets as $ticket) {
            $total += $this->calculPriceTicket($ticket);
        }
        return $total;
    }

    const REDUCE_PRICE = 10;

    public function calculPriceTicket($ticket)
    {
        // if price reduced
        if($ticket->getReduced() === true) {
            $price = BookingManager::REDUCE_PRICE;
        } else { // standard price ticket
            $price = $ticket->getPrice();
        }
        // if half day
        if($ticket->getDescription() === 'half day ticket') $price = $price/2;

        return $price;
    }

    public function setReservationId($id)
    {
        //Je recupere l'objet session de symfony et j'affecte la valeur reservationId à l'attribut
        $this->session->set('reservationId', $id);

        // reviens au même que :
        //$session = $this->session;
        //$session->set('reservationId',$id);

    }

    public function getReservationId()
    {
       $id = $this->session->get('reservationId');
        return $id;
    }

}



