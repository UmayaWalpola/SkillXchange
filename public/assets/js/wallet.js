        // Sample data storage
        let balance = 150;
        let totalSent = 75;
        let totalReceived = 225;
        let sentTransactions = [
            { receiver: "John Doe", amount: 25, timestamp: "2 hours ago", id: 1 },
            { receiver: "Jane Smith", amount: 50, timestamp: "1 day ago", id: 2 }
        ];
        let receivedTransactions = [
            { sender: "Alice Johnson", amount: 100, timestamp: "3 hours ago", id: 1 },
            { sender: "Bob Wilson", amount: 125, timestamp: "2 days ago", id: 2 }
        ];

        // Update display function
        function updateDisplay() {
            // Update stats
            document.getElementById('balanceAmount').textContent = balance;
            document.getElementById('totalSent').textContent = totalSent;
            document.getElementById('totalReceived').textContent = totalReceived;
            
            // Update counts
            document.getElementById('sentCount').textContent = sentTransactions.length;
            document.getElementById('receivedCount').textContent = receivedTransactions.length;

            // Update sent transactions
            const sentList = document.getElementById('sentTransactions');
            sentList.innerHTML = sentTransactions.map(tx => 
                `<div class="transaction-item">
                    <div class="transaction-info">
                        <div class="transaction-user">${tx.receiver}</div>
                        <div class="transaction-time">${tx.timestamp}</div>
                    </div>
                    <div class="transaction-amount sent">-${tx.amount} BuckX</div>
                </div>`
            ).join('');

            // Update received transactions
            const receivedList = document.getElementById('receivedTransactions');
            receivedList.innerHTML = receivedTransactions.map(tx => 
                `<div class="transaction-item">
                    <div class="transaction-info">
                        <div class="transaction-user">${tx.sender}</div>
                        <div class="transaction-time">${tx.timestamp}</div>
                    </div>
                    <div class="transaction-amount received">+${tx.amount} BuckX</div>
                </div>`
            ).join('');
        }

        // Handle form submission
        document.getElementById('transferForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const recipient = document.getElementById('recipient').value;
            const amount = parseInt(document.getElementById('amount').value);
            const note = document.getElementById('note').value;

            if (amount > balance) {
                alert('&#10060; Insufficient balance! Please enter a smaller amount.');
                return;
            }

            if (amount <= 0) {
                alert('&#10060; Please enter a valid amount.');
                return;
            }

            // Add loading state
            const submitBtn = this.querySelector('.send-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span>&#9203;</span><span>Sending...</span>';
            submitBtn.disabled = true;

            // Simulate API call delay
            setTimeout(() => {
                // Update balance and totals
                balance -= amount;
                totalSent += amount;

                // Add to sent transactions
                sentTransactions.unshift({
                    receiver: recipient,
                    amount: amount,
                    timestamp: "Just now",
                    id: Date.now()
                });

                // Keep only last 10 transactions
                if (sentTransactions.length > 10) {
                    sentTransactions = sentTransactions.slice(0, 10);
                }

                // Reset form
                this.reset();
                
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;

                // Update display
                updateDisplay();

                // Success message
                alert(`&#9989; Successfully sent ${amount} BuckX to ${recipient}!`);
            }, 1500);
        });

        // Initialize display
        updateDisplay();

        // Add real-time updates simulation
        setInterval(() => {
            // Randomly receive money (simulation)
            if (Math.random() < 0.1) { // 10% chance every 30 seconds
                const senders = ['Mike Johnson', 'Sarah Davis', 'Tom Brown', 'Lisa White'];
                const amounts = [10, 15, 20, 25, 30, 50];
                
                const sender = senders[Math.floor(Math.random() * senders.length)];
                const amount = amounts[Math.floor(Math.random() * amounts.length)];
                
                balance += amount;
                totalReceived += amount;
                
                receivedTransactions.unshift({
                    sender: sender,
                    amount: amount,
                    timestamp: "Just now",
                    id: Date.now()
                });
                
                if (receivedTransactions.length > 10) {
                    receivedTransactions = receivedTransactions.slice(0, 10);
                }
                
                updateDisplay();
                
                // Show notification
                alert(`&#127881; You received ${amount} BuckX from ${sender}!`);
            }
        }, 30000); // Check every 30 seconds