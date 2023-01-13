self.addEventListener("activate", async () => {
    console.log("Service Worker: Activated");
});

self.addEventListener("push", (event) => {
    const data = JSON.parse(event.data.text());
    if (data.type === "newMessage") {
        event.waitUntil(
            self.registration.showNotification(data.from, {
                body: data.from + " sent you a message!",
                icon: data.icon,
                actions: [
                    { action: "open", title: "Open" },
                    { action: "close", title: "Close" },
                ],
            })
        );
    } else if (data.type === "newGroupMessage") {
        event.waitUntil(
            self.registration.showNotification(data.from, {
                body: "New message in " + data.from,
                icon: data.icon,
                actions: [
                    { action: "open", title: "Open" },
                    { action: "close", title: "Close" },
                ],
            })
        );
    } else if (data.type === "friendRequest") {
        event.waitUntil(
            self.registration.showNotification(data.from, {
                body: data.from + " requested to be your friend!",
                icon: data.icon,
                actions: [
                    { action: "open", title: "Open" },
                    { action: "close", title: "Close" },
                ],
            })
        );
    } else if (data.type === "friendRequestAccepted") {
        event.waitUntil(
            self.registration.showNotification(data.from, {
                body: data.from + " accepted your friend request!",
                icon: data.icon,
                actions: [
                    { action: "open", title: "Open" },
                    { action: "close", title: "Close" },
                ],
            })
        );
    }
    self.clients
        .matchAll({
            includeUncontrolled: true,
            type: "window",
        })
        .then((clients) => {
            if (clients && clients.length) {
                clients[0].postMessage(data);
            }
        });
});

self.addEventListener(
    "notificationclick",
    function (event) {
        if (event.action === "open") {
            event.notification.close();
            clients.openWindow("https://localhost:8002");
        } else if (event.action === "close") {
            event.notification.close();
        }
    },
    false
);
