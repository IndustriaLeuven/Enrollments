Vagrant.configure(2) do |config|
    config.vm.box = "debian/jessie64"
    config.vm.synced_folder ".", "/vagrant", type: "virtualbox"

    config.vm.provision :shell, path: "provisioning/install-ansible.sh"
    config.vm.provision :shell, inline: "PYTHONUNBUFFERED=1 sudo ansible-playbook /vagrant/provisioning/all.yml --connection=local"

    config.vm.define "enrollments", primary: true do |enrollments|
        enrollments.vm.network "private_network", ip: "192.168.80.7"
        enrollments.vm.synced_folder "src", "/var/www/src", create: true
        enrollments.vm.synced_folder "documentation", "/var/www/documentation", create: true
        enrollments.vm.provision :shell, inline: "PYTHONUNBUFFERED=1 sudo ansible-playbook /vagrant/provisioning/enrollments.yml --connection=local"
    end

    config.vm.define "authserver", autostart: false do |authserver|
        authserver.vm.network "private_network", ip: "192.168.80.2"
        authserver.vm.provision :shell, inline: "PYTHONUNBUFFERED=1 sudo ansible-playbook /vagrant/provisioning/authserver.yml --connection=local"
    end

    config.vm.provider "virtualbox" do |vb|
        vb.gui = false
        vb.cpus = 1
        vb.memory = 512
    end

end
