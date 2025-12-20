import { useQuery } from "@tanstack/react-query";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { getAdminPackages, getAdminInquiries } from "@/lib/adminApi";
import { Package, Mail, DollarSign, TrendingUp } from "lucide-react";

export default function AdminDashboard() {
  const { data: packages = [] } = useQuery({
    queryKey: ['admin-packages'],
    queryFn: () => getAdminPackages(),
  });

  const { data: inquiriesData } = useQuery({
    queryKey: ['admin-inquiries-stats'],
    queryFn: () => getAdminInquiries({ limit: 1 }),
  });

  const stats = inquiriesData?.stats;
  const activePackages = packages.filter(p => p.status === 'active').length;
  const totalPackages = packages.length;

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl font-heading font-bold text-primary mb-2">Dashboard</h1>
        <p className="text-muted-foreground">Welcome to Sinath Travels Admin Panel</p>
      </div>

      {/* Statistics Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard
          title="Total Packages"
          value={totalPackages}
          icon={<Package className="w-8 h-8" />}
          bgColor="bg-blue-500/10"
          iconColor="text-blue-500"
        />
        
        <StatCard
          title="Active Packages"
          value={activePackages}
          icon={<TrendingUp className="w-8 h-8" />}
          bgColor="bg-green-500/10"
          iconColor="text-green-500"
        />
        
        <StatCard
          title="Total Inquiries"
          value={stats?.total || 0}
          icon={<Mail className="w-8 h-8" />}
          bgColor="bg-purple-500/10"
          iconColor="text-purple-500"
        />
        
        <StatCard
          title="New Inquiries"
          value={stats?.new_count || 0}
          icon={<Mail className="w-8 h-8" />}
          bgColor="bg-orange-500/10"
          iconColor="text-orange-500"
          highlight={true}
        />
      </div>

      {/* Recent Activity */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Quick Stats</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <div className="flex items-center justify-between pb-3 border-b">
                <span className="text-sm text-muted-foreground">Tour Packages</span>
                <span className="font-bold text-primary">
                  {packages.filter(p => p.category === 'tour').length}
                </span>
              </div>
              <div className="flex items-center justify-between pb-3 border-b">
                <span className="text-sm text-muted-foreground">Visa Services</span>
                <span className="font-bold text-primary">
                  {packages.filter(p => p.category === 'visa').length}
                </span>
              </div>
              <div className="flex items-center justify-between pb-3 border-b">
                <span className="text-sm text-muted-foreground">Read Inquiries</span>
                <span className="font-bold text-primary">{stats?.read_count || 0}</span>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-sm text-muted-foreground">Replied Inquiries</span>
                <span className="font-bold text-green-600">{stats?.replied_count || 0}</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>System Status</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <StatusItem label="Database" status="online" />
              <StatusItem label="API Server" status="online" />
              <StatusItem label="Image Uploads" status="online" />
              <StatusItem label="Email Service" status="pending" />
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}

function StatCard({ 
  title, 
  value, 
  icon, 
  bgColor, 
  iconColor,
  highlight = false 
}: { 
  title: string; 
  value: number; 
  icon: React.ReactNode; 
  bgColor: string; 
  iconColor: string;
  highlight?: boolean;
}) {
  return (
    <Card className={highlight ? "border-orange-500 border-2" : ""}>
      <CardContent className="p-6">
        <div className="flex items-start justify-between">
          <div className="flex-1">
            <p className="text-sm text-muted-foreground mb-2">{title}</p>
            <p className="text-3xl font-bold text-primary">{value}</p>
          </div>
          <div className={`${bgColor} ${iconColor} p-3 rounded-lg`}>
            {icon}
          </div>
        </div>
      </CardContent>
    </Card>
  );
}

function StatusItem({ label, status }: { label: string; status: 'online' | 'offline' | 'pending' }) {
  const colors = {
    online: 'bg-green-500',
    offline: 'bg-red-500',
    pending: 'bg-yellow-500',
  };

  const labels = {
    online: 'Online',
    offline: 'Offline',
    pending: 'Not Configured',
  };

  return (
    <div className="flex items-center justify-between pb-3 border-b last:border-0 last:pb-0">
      <span className="text-sm text-muted-foreground">{label}</span>
      <div className="flex items-center gap-2">
        <div className={`w-2 h-2 rounded-full ${colors[status]}`} />
        <span className="text-sm font-medium">{labels[status]}</span>
      </div>
    </div>
  );
}