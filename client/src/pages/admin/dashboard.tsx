import { useQuery } from "@tanstack/react-query";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { Package, Mail, TrendingUp, AlertCircle, RefreshCw } from "lucide-react";
import { Button } from "@/components/ui/button";

export default function AdminDashboard() {
  const { data, isLoading, error, refetch, isFetching } = useQuery({
    queryKey: ['admin-dashboard'],
    queryFn: async () => {
      const API_BASE = import.meta.env.VITE_API_URL?.replace('/api', '/api/admin') ||
        '/api/admin';

      const token = localStorage.getItem('admin_token');
      const headers: HeadersInit = {};
      if (token) {
        headers['Authorization'] = `Bearer ${token}`;
      }

      const response = await fetch(`${API_BASE}/dashboard.php`, {
        headers,
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const result = await response.json();
      return result.data;
    },
  });

  if (isLoading) {
    return (
      <div className="space-y-8">
        <h1 className="text-3xl font-bold">Dashboard</h1>
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          {[1, 2, 3, 4].map(i => (
            <Card key={i}><CardContent className="p-6">
              <Skeleton className="h-20 w-full" />
            </CardContent></Card>
          ))}
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <Alert variant="destructive">
        <AlertCircle className="h-4 w-4" />
        <AlertDescription>
          Failed to load dashboard: {error.toString()}
          <Button onClick={() => refetch()} variant="outline" size="sm" className="ml-4">
            Retry
          </Button>
        </AlertDescription>
      </Alert>
    );
  }

  const packages = data?.packages || {};
  const inquiries = data?.inquiries || {};

  return (
    <div className="space-y-8">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-bold">Dashboard</h1>
        <Button
          onClick={() => refetch()}
          variant="outline"
          size="sm"
          disabled={isFetching}
        >
          <RefreshCw className={`w-4 h-4 mr-2 ${isFetching ? 'animate-spin' : ''}`} />
          {isFetching ? 'Refreshing...' : 'Refresh'}
        </Button>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <StatCard
          title="Total Packages"
          value={packages.total_packages || 0}
          icon={<Package className="w-8 h-8" />}
          color="blue"
        />
        <StatCard
          title="Active Packages"
          value={packages.active_packages || 0}
          icon={<TrendingUp className="w-8 h-8" />}
          color="green"
        />
        <StatCard
          title="Total Inquiries"
          value={inquiries.total_inquiries || 0}
          icon={<Mail className="w-8 h-8" />}
          color="purple"
        />
        <StatCard
          title="New Inquiries"
          value={inquiries.new_inquiries || 0}
          icon={<Mail className="w-8 h-8" />}
          color="orange"
          highlight
        />
      </div>

      {/* Breakdown Cards */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Package Breakdown</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3">
            <Row label="Tour Packages" value={packages.tour_packages || 0} />
            <Row label="Visa Services" value={packages.visa_packages || 0} />
            <Row label="Flight Tickets" value={packages.ticket_packages || 0} />
            <Row label="Special Offers" value={packages.offer_packages || 0} />
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Inquiry Status</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3">
            <Row label="New" value={inquiries.new_inquiries || 0} color="orange" />
            <Row label="Read" value={inquiries.read_inquiries || 0} color="blue" />
            <Row label="Replied" value={inquiries.replied_inquiries || 0} color="green" />
          </CardContent>
        </Card>
      </div>
    </div>
  );
}

function StatCard({ title, value, icon, color, highlight }: {
  title: string;
  value: number;
  icon: React.ReactNode;
  color: 'blue' | 'green' | 'purple' | 'orange';
  highlight?: boolean;
}) {
  const colors = {
    blue: 'bg-blue-500/10 text-blue-500',
    green: 'bg-green-500/10 text-green-500',
    purple: 'bg-purple-500/10 text-purple-500',
    orange: 'bg-orange-500/10 text-orange-500',
  };

  return (
    <Card className={highlight ? "border-orange-500 border-2" : ""}>
      <CardContent className="p-6">
        <div className="flex justify-between items-start">
          <div>
            <p className="text-sm text-muted-foreground mb-2">{title}</p>
            <p className="text-3xl font-bold">{value}</p>
          </div>
          <div className={`p-3 rounded-lg ${colors[color]}`}>
            {icon}
          </div>
        </div>
      </CardContent>
    </Card>
  );
}

function Row({ label, value, color }: {
  label: string;
  value: number;
  color?: 'orange' | 'blue' | 'green';
}) {
  const colors = {
    orange: 'text-orange-600',
    blue: 'text-blue-600',
    green: 'text-green-600',
  };

  return (
    <div className="flex justify-between items-center pb-3 border-b last:border-0">
      <span className="text-sm text-muted-foreground">{label}</span>
      <span className={`font-bold ${color ? colors[color] : ''}`}>
        {value}
      </span>
    </div>
  );
}