<?php
class WalletController extends Controller {
    public function index() {
        // Sample data (later replace with DB queries)
        $data = [
            "balance" => 150,
            "totalSent" => 75,
            "totalReceived" => 225,
            "sentTransactions" => [
                ["receiver" => "John Doe", "amount" => 25, "timestamp" => "2 hours ago"],
                ["receiver" => "Jane Smith", "amount" => 50, "timestamp" => "1 day ago"],
            ],
            "receivedTransactions" => [
                ["sender" => "Alice Johnson", "amount" => 100, "timestamp" => "3 hours ago"],
                ["sender" => "Bob Wilson", "amount" => 125, "timestamp" => "2 days ago"],
            ]
        ];

        $this->view("wallet/wallet", $data);
    }
}
